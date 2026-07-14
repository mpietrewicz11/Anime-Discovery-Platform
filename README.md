# ADEM — Anime Discovery & Engagement Manager

A distributed web application built across 8 Linux virtual machines spanning production, QA, and development environments. Built for IT-490 (Distributed Systems) at NJIT.

## Architecture

| VM | Role | IP |
|---|---|---|
| Prod Backend | RabbitMQ, MySQL, PHP backend | 100.67.69.11 |
| Prod Frontend | Apache, PHP web server | 100.76.15.56 |
| QA Backend | QA environment backend | 100.107.182.2 |
| QA Frontend | QA environment web server | 100.116.21.106 |
| Dev Backend | Dev environment backend | 100.96.224.82 |
| Dev Frontend | Dev environment web server | 100.82.248.26 |
| Deploy VM | Deployment server, bundle storage | 100.64.95.105 |
| DMZ | Apache reverse proxy | 100.90.102.9 |

All VMs communicate over a private Tailscale VPN.

## Deliverables Completed

**Midterm**
- Functional web application with registration, login, and session management
- Database secured with no direct remote access — all communication routed through RabbitMQ
- Inter-server communication via RabbitMQ message broker
- Automated anime data collection via cron job running every 30 minutes against the Jikan API
- Firewall rules configured on all VMs with reject defaults
- Full authentication system with bcrypt hashed passwords and email MFA

**Anime Features**
- Browse and search anime
- Rate and review anime
- Watchlist management
- Recommendation system based on genre
- Email push notifications for anime releases
- Comment section on anime pages

**Final**
- systemd managing all custom services with automatic restart
- Custom deployment pipeline with versioned releases, one-command rollback, and deployment history tracked in MySQL
- Production, QA, and development clusters across 8 VMs
- Decentralized logging — events broadcast to all VMs simultaneously via RabbitMQ fanout exchange
- SSL/HTTPS on production web server
- Responsive web design for mobile and desktop
- Social discussion feed with posts, reposts, and likes
- Public user profiles showing posts, watchlist, and reviews

## Deployment System

- `deploy/builder.sh` — packages webserver files into a versioned `.tar.gz` bundle and ships it to the deploy VM
- `deploy/deployClient.php` — sends a RabbitMQ message to trigger deployment to a target environment
- `backend/deployServer.php` — receives deployment requests, extracts bundles, pushes files to target VMs, restarts Apache, and records deployment history in MySQL
- Supports deploy, rollback, and mark-as-bad operations across dev, QA, and production

## Decentralized Logging

Log events are published to a RabbitMQ fanout exchange (`logs.exchange`) and broadcast to every VM simultaneously. Each VM runs `decentralizedLogger.php` as a systemd service and writes events to `/var/log/it490.log` locally.

## Tech Stack

- **Backend:** PHP 8.1, RabbitMQ (AMQP), MySQL 8.0
- **Frontend:** PHP 8.3, Apache2
- **Infrastructure:** Linux (Ubuntu 22.04/24.04), systemd, Tailscale, Bash
- **Deployment:** Custom shell/PHP pipeline, rsync, SSH

## My Contributions

- Designed and built the entire backend infrastructure (RabbitMQ, MySQL, PHP backend services)
- Built the deployment pipeline end to end (builder.sh, deployClient.php, deployServer.php)
- Configured all three backend environments (Prod, QA, Dev)
- Implemented decentralized logging across all VMs
- Set up systemd services for automatic process management
- Implemented bcrypt password hashing and MFA
- Configured MySQL, RabbitMQ vhosts, exchanges, and queues
- Set up Tailscale VPN networking across all 8 VMs
- Built the discussion/feed feature and public profile system

## Branch

All work is on the `Final` branch.
