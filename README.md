# Politik test task
1. EP_IMPORT_URL should be defined in ENV
2. Import CLI command:
```php
php bin/console app:import-ep --contact_import=queue
```
3Run messenger consumer:
```php
php bin/console messenger:consume async_contact
```