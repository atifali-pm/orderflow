# Orderflow

A small order management application with first-class n8n automation. Place an order in the UI, watch a workflow run multi-step automation (invoicing, notifications, CRM sync), and see the order update itself via callback within seconds.

## Why this exists

Most order tools either run pure manual ops in a CRUD UI or push everything through external SaaS automation that you cannot inspect. Orderflow keeps the order surface inside a Laravel + Livewire app you control, and parks an n8n instance next to it so the automation layer is a workflow JSON you can edit, version, and swap. Every order event fires through n8n; n8n calls back into the app via API. The whole stack runs from a single docker-compose with Laravel, Postgres, Redis, n8n, and Mailpit. No external hosting needed to demo.

## Features

- Livewire 3 order surface: create, list, view, update orders with line items and customers
- Event-driven outbound webhooks to n8n with HMAC signature verification
- Inbound REST API for n8n callbacks, protected by per-workflow API token and idempotency key
- AutomationLog timeline rendered in the Livewire order view so every n8n step is visible
- Demo workflow committed at `n8n/workflows/order-placed.json` so anyone running the stack gets the same flow
- Horizon dashboard for queue monitoring, Mailpit for local mail capture
- Everything boots from one docker-compose

## Demo flow

1. Ops user creates an order in the Livewire UI (customer, line items, total).
2. Laravel dispatches `OrderPlaced`. The queue worker POSTs the payload to the `order-placed` webhook in n8n with an HMAC signature.
3. n8n workflow runs: branch on total (if over $500 send Slack alert), send confirmation email via Mailpit, generate a fake invoice number, POST it back to `/api/orders/{id}/invoice`, log each step at `/api/orders/{id}/automation-log`.
4. The Livewire dashboard shows the invoice number and automation timeline within seconds.

## Phases

- Phase 0 (scaffolded): repo structure, docker-compose stub with five services, memory, README, HANDS-ON, .env.example
- Phase 1: domain model and Livewire CRUD without n8n
- Phase 2: outbound integration. Events, queue, HMAC dispatcher, manual n8n flow
- Phase 3: inbound integration. API endpoints, idempotency, AutomationLog, committed workflow JSON
- Phase 4: polish. Timeline UI, dashboard cards, recorded demo, screenshots
- Phase 5 (optional): hosting if there is a reason

## Screenshots

Screenshots land in [`/screenshots/`](screenshots/) once the UI is functional (Phase 1 onward).

<!-- Example embeds, fill in as screenshots arrive:
![Orders index](screenshots/01-orders-index.png)
![Order create](screenshots/02-order-create.png)
![Order timeline with n8n results](screenshots/03-order-timeline.png)
-->
