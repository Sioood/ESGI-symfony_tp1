# ESGI-symfony_tp1

### Edit .env database path

```
DATABASE_URL="mysql://root@127.0.0.1:3306/symfony-tp1?serverVersion=8.0.32&charset=utf8mb4"
```

### Create the database
```bash
php bin/console doctrine:database:create
```

### Migrate
```bash
php bin/console doctrine:migrations:migrate
```

### Start the server
```bash
symfony server:start
```