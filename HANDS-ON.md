# Orderflow hands-on

Kickoff guide for the next Claude Code session. Read top to bottom before running anything.

## Screenshots

When you hit a UI milestone (orders index renders, create-order form works, automation timeline shows real n8n results), capture a screenshot and save it to `/screenshots/` at the repo root. Use descriptive filenames like `01-orders-index.png`, `02-order-create.png`, `03-order-timeline.png`.

Embed every screenshot in README.md via relative markdown image refs: `![Orders index](screenshots/01-orders-index.png)`. A public repo with screenshots embedded in the README is a complete portfolio artifact. A live deploy URL is optional.

`/screenshots/` is the one canonical location for source image files. Do not duplicate them into `/docs/` or `/public/`. The portfolio-maintainer at `~/projects/portfolio/.claude/agents/portfolio-maintainer.md` looks in `/screenshots/` when deciding whether to promote the project.

## Preflight

Before starting Phase 1, verify the host has what it needs.

- Docker and Docker Compose installed and running (`docker compose version`).
- Ports free on the host: 8050 (app), 5457 (postgres), 6383 (redis), 5679 (n8n), 8025 (mailpit web UI). Run `ss -tlnp | grep -E '8050|5457|6383|5679|8025'` and confirm zero output.
- PHP 8.3 and Composer available locally if you plan to run `composer create-project` from the host rather than inside the container.
- Node 20+ and npm if you plan to run Tailwind builds on the host.

## First-run kickoff prompt for Phase 1

Paste this in a fresh Claude Code session run from `/home/atif/projects/orderflow/`:

```
Phase 1 of Orderflow. Read HANDS-ON.md and the per-project memory at ~/.claude/projects/-home-atif-projects-orderflow/memory/project_orderflow.md before you touch anything.

Goals for this phase:
1. Install Laravel 11 fresh into this directory: `composer create-project laravel/laravel:^11.0 .` (the repo root is empty of Laravel files right now; the docker-compose stub, README, HANDS-ON, n8n dir, and screenshots dir must survive the install).
2. Install Breeze with the Livewire + Alpine flavor: `php artisan breeze:install livewire`.
3. Install Pest as the test framework: `composer remove phpunit/phpunit --dev && composer require pestphp/pest pestphp/pest-plugin-laravel --dev --with-all-dependencies` then `./vendor/bin/pest --init`.
4. Wire Postgres as the default DB connection in config/database.php and .env (host=postgres, port=5432, db=orderflow, user=orderflow, pass=orderflow). Redis as cache + session + queue driver. Mailpit at host=mailpit, port=1025, no auth.
5. Build the domain model: migrations for `customers`, `orders`, `order_items`. Models with relationships. A `DemoSeeder` that creates a handful of customers and products so the UI has something to render.
6. Build the Livewire surfaces: `Orders/Index` (paginated list with status filters), `Orders/Create` (form with line items, computes total), `Orders/Show` (read-only detail with a placeholder area for the automation timeline that Phase 3 will fill).
7. Tailwind + DaisyUI configured. Layout uses Breeze's authenticated layout. Navigation has Dashboard + Orders + Customers links.
8. Pest feature test that exercises end-to-end create-order via Livewire. Should pass.

Out of scope for Phase 1: no n8n wiring, no events, no API routes, no HMAC, no AutomationLog model. Those are Phases 2 and 3.

When you finish, drop screenshots of the orders index and create-order form into /screenshots/, embed them in README.md, and commit. No Co-Authored-By trailers, no AI attribution anywhere in commits or docs.
```

## Full phase plan

- [x] **Phase 0**: scaffold dirs, memory, docker-compose stub with five services, README, HANDS-ON, .env.example, .gitignore, first push (this commit).
- [x] **Phase 1**: Laravel 11 install, Breeze (Livewire+Alpine), Pest, domain model, Livewire CRUD, DemoSeeder, navigation. End-to-end create-order flow without n8n.
- [ ] **Phase 2**: Outbound integration. `OrderPlaced` / `OrderPaid` / `OrderShipped` / `OrderCancelled` events, `SyncToN8nJob`, `WebhookDispatcher`, `HmacSigner`, Horizon, .env wiring. Manual n8n flow that logs payload.
- [ ] **Phase 3**: Inbound integration. `/api/orders/*` endpoints, `VerifyN8nApiToken` middleware, idempotency-key handling, `AutomationLog` model, commit `n8n/workflows/order-placed.json`.
- [ ] **Phase 4**: Polish. Livewire timeline view that polls AutomationLog, dashboard cards, screen-capture demo recording, more screenshots into /screenshots/.
- [ ] **Phase 5 (optional)**: Railway or Fly deploy if there is a reason.

## Known gotchas

- Port 5678 is already taken on Atif's machine by inbox-ops / n8n-agent-studio. Orderflow's n8n service exposes 5679 on the host but listens on its default 5678 inside the docker network. `N8N_WEBHOOK_URL_BASE` for in-cluster Laravel calls uses `http://n8n:5678/`; external (host-to-n8n) calls use `http://localhost:5679/`.
- Postgres on the host is at port 5457 to avoid conflict with the documented port map (caseflow 5434, zarpay 5450, nike-proxy 5433, inbox-ops 5451, meridian 5455, rampart 5456).
- Mailpit SMTP port 1025 stays internal to the docker network. Laravel's `MAIL_HOST=mailpit` `MAIL_PORT=1025`. The web UI is exposed at host port 8025.
- `composer create-project` will refuse to install into a non-empty directory. Workflow for Phase 1: temporarily move docker/, n8n/, screenshots/, README.md, HANDS-ON.md, .env.example, .gitignore out, run the install, move them back. Or use `composer create-project laravel/laravel:^11.0 tmp_laravel && rsync -a tmp_laravel/ . && rm -rf tmp_laravel`.
- HMAC payload signing must use a stable JSON serialization (sorted keys, no trailing whitespace) so n8n can verify deterministically. Note this when Phase 2 lands.
- Idempotency keys for inbound n8n callbacks should be stored in a Redis set with a 24h TTL, not the database, to keep the write path fast.
