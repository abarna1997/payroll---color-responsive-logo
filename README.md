# Attendance & Workforce Management System (WMS)
### Enterprise Biometric Timekeeping, Advanced Scheduling & Statutory Payroll Platform

[![Laravel 12](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-8.0%2B-4479A1?style=for-the-badge&logo=mysql)](https://mysql.com)
[![ZKTeco](https://img.shields.io/badge/ZKTeco-SenseFace_M2F--LR-007ACC?style=for-the-badge)](https://zkteco.com)
[![License: Proprietary](https://img.shields.io/badge/License-Proprietary-red.style=for-the-badge)](#)

---

## Overview

The **Attendance Management System (AMS)** is an enterprise-grade platform engineered to unify physical IoT biometric terminals (ZKTeco SenseFace M2F-LR), multi-company workforce administration, advanced shift scheduling, and Sri Lankan statutory payroll compliance (EPF, ETF, and IRD APIT tax regulations).

Developed for **Prime One Global**, **Altitude 1**, and affiliated multi-tier organizations, the platform delivers real-time attendance processing, automated hardware synchronization, and a self-service employee portal.

---

## Key System Capabilities

- **Biometric Push Protocol Engine**: Direct HTTP Push integration (`/iclock/*` and `/adms/*`) supporting real-time face, fingerprint, RFID, and PIN scans with zero client-side middleware.
- **Automated Device Auto-Discovery & Command Queue**: Real-time terminal auto-registration, two-factor administrator approval, automated biometric template synchronization, and remote firmware/storage telemetry.
- **Advanced Shift & Roster Engine**: Full support for standard day, split, and cross-midnight overnight shifts with dynamic punch windows, tolerance grace periods, and half-day boundary detection.
- **Workforce Lifecycle Management**: Digital onboarding wizard, employee asset allocation with PDF handover receipts, digital contract signing, and formal transfer/separation workflows.
- **Sri Lankan Statutory Payroll Engine**: Automated monthly payroll runs calculating EPF (Employee 8%, Employer 12%), ETF (Employer 3%), and Inland Revenue Department (IRD) progressive APIT tax brackets with PDF payslips and bank export files.
- **Self-Service Employee Portal**: Web punch clock-in with geofencing verification, WFH application, leave tracking, and digitally signed payslip downloads.
- **Enterprise Governance & Security**: 3-tiered RBAC (Roles, Access Levels, Permission Scopes), immutable audit logs, and WSO2 / Authentik SSO integration.

---

## Quick Start Guide

### 1. Requirements
- PHP 8.2 or 8.3 (with `bcmath`, `curl`, `gd`, `mbstring`, `pdo_mysql`, `xml`, `zip`, `sockets`)
- MySQL 8.0+ or MariaDB 10.4+
- Composer 2.x
- Node.js 18+ and NPM

### 2. Local Setup
```bash
# 1. Clone repository
git clone https://github.com/Prime1-ITD/wms.git
cd wms

# 2. Automated project setup (installs composer, creates .env, generates app key, runs migrations, builds assets)
composer setup

# 3. Launch unified development environment (Server, Queue, Logs, and Vite)
composer dev
```

### 3. Production Health Verification
To run the automated 8-phase system readiness test:
```bash
php artisan app:validate-production
```

---

## Enterprise Documentation Suite

Comprehensive technical, architectural, and operational documentation is maintained in the [`docs/`](docs/) directory:

| Document | Focus Area | Intended Audience |
| :--- | :--- | :--- |
| 📑 **[01: Project Handover Document](docs/01_PROJECT_HANDOVER_DOCUMENT.md)** | Transition roadmap, stakeholders, module inventory, codebase layout, and sign-off checklist | Engineering Leads, Product Owners, Incoming Developers |
| 🏛️ **[02: System Reference Document (SRD)](docs/02_SYSTEM_REFERENCE_DOCUMENT.md)** | Architecture topology, comprehensive database schema (ERDs), API catalog, RBAC matrix, and crons | Software Architects, Senior Developers, DBA |
| ⚙️ **[03: Technical Specification (Tech Spec)](docs/03_TECHNICAL_SPECIFICATION_DOCUMENT.md)** | ADMS protocol mechanics, shift resolution hierarchy, statutory payroll formulas (EPF/APIT), and query benchmarks | Core Backend Engineers, Algorithm Specialists |
| 🚀 **[04: Runbook & Deployment Guide](docs/04_DEPLOYMENT_RUNBOOK.md)** | Production deployment (cPanel & Ubuntu VPS), ZKTeco hardware configuration, backup, and disaster recovery | DevOps Engineers, SysAdmins, Field IT Support |
| 📖 **[05: HR & User Operations Manual](docs/05_USER_MANUAL_AND_HR_OPERATIONS_GUIDE.md)** | Step-by-step UI guides for employee onboarding, shift rostering, attendance adjustments, and payroll execution | HR Managers, Payroll Officers, Operations Leads |
| 🧪 **[06: QA & Testing Playbook](docs/06_QA_AND_UAT_TESTING_PLAYBOOK.md)** | End-to-end UAT test cases, automated validation commands, mock hardware simulations, and edge cases | QA Engineers, Test Leads, UAT Evaluators |
| 🗄️ **[07: Database Data Dictionary](docs/07_DATABASE_DATA_DICTIONARY.md)** | Detailed column-level definitions, data types, indexes, and foreign key relations for all core tables | Database Administrators, Data Analysts |
| 🔮 **[08: Future Development Roadmap](docs/08_FUTURE_DEVELOPMENT_ROADMAP.md)** | Strategic product roadmap, technical debt reduction, AI liveness, mobile offline sync, and V3.0 scaling | Product Owners, Lead Architects, Executive Team |

---

## Biometric Hardware Compatibility

| Hardware Model | Communication Protocol | Firmware / SDK | Supported Features |
| :--- | :--- | :--- | :--- |
| **ZKTeco SenseFace M2F-LR** | HTTP Push (ADMS) | Push SDK 2.0+ | Face Recognition, Fingerprint, RFID Card, PIN, Real-Time Push, Remote Command Dispatch |
| **ZKTeco SilkBio / uFace Series** | HTTP Push (ADMS) | Push SDK 1.0 / 2.0 | Fingerprint, Face, Card, Remote Enrollment |
| **Browser Web Punch** | HTTPS (REST) | Geolocation API | Geofenced Clock-In, IP Allowlisting, Selfie Verification |

---

## License & Intellectual Property

This software and related documentation are proprietary to **Prime One Global** and **Altitude 1**. All rights reserved. Unauthorized copying, distribution, or decompilation is strictly prohibited.
