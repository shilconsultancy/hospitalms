# HospitalMS (PHP & MySQL)

Raw PHP Hospital Management System skeleton following the provided project plan.

## Setup

1. Start Apache & MySQL in XAMPP.
2. Create DB and tables:
   ```sql
   SOURCE database/database.sql;
   ```
3. Configure base URL and DB via environment variables or edit `config/*.php`.
4. Visit `http://localhost/hospitalMS/public`.

## Development
- PSR-4 autoload: `App\` -> `src/`
- Front controller at `public/index.php`
- Run tests: `./vendor/bin/phpunit`

## Security & Performance (Phase 5)
- Use prepared statements (PDO) everywhere (already enforced via Database connector defaults)
- Set proper permissions on `logs/` and `storage/`
- Configure `session.cookie_httponly=1` and use HTTPS in production
- Add indexes: email on `users`, foreign keys (already), `appointments(patient_id, doctor_id, scheduled_at)`, `admissions(patient_id, bed_id)`
- Enable OPcache in PHP and gzip on Apache; consider Redis for sessions/caching

## Deployment (Phase 6)
1. Copy code to `/var/www/hospitalMS` (or preferred path)
2. Set vhost DocumentRoot to `public/`
3. Import schema: `mysql -u root -p < database/database.sql`
4. Set environment variables in Apache vhost or a secure `.env` mechanism
5. Ensure `logs/` and `storage/` are writable by web server user

## BI & KPIs
- Bed Occupancy Rate = occupied beds / total beds
- ALOS (Average Length Of Stay) = total inpatient days / discharges
- Appointments per doctor per day
