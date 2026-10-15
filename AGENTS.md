# AGENTS.md

This repository uses `.agents/skills/` as the canonical project-local skills system for My Ecommerce (`apps`).

## Source Map

- `.agents/skills/apps-project-overview/`: Monorepo architecture and project overview
- `.agents/skills/apps-dashboard-backend/`: Laravel 11 & FilamentPHP 3 backend rules
- `.agents/skills/apps-website-frontend/`: React 19 customer storefront rules
- `.agents/skills/apps-database/`: Schema design, migrations, Eloquent, and transactions
- `.agents/skills/apps-api-contracts/`: REST API contracts under `/api/v1/`
- `.agents/skills/apps-business-workflows/`: Task-based routing for e-commerce domain flows

## Start Here

1. Read this file.
2. Read `.agents/skills/apps-project-overview/SKILL.md` to establish application boundaries (`dashboard/` vs `website/`).
3. If working on a specific domain flow, use `.agents/skills/apps-business-workflows/SKILL.md`.
4. Follow strict layer integrity:
   - Backend: `Controller / Resource → Service → Action → Repository → Model`
   - Frontend: `Component (UI) ↔ Custom Hook (Logic) ↔ Service (API)`
5. Verify changes with targeted test runs (`php artisan test` or `npm test`).

