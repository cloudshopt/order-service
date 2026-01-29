## order-service
- **Purpose:** Cart + order creation and order history.
- **Base path:** `/api/orders`

### Create product database
```
kubectl -n cloudshopt exec -it cloudshopt-mysql-0 -- bash

# mysql -u root -prootpass

CREATE DATABASE cloudshopt_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'users'@'%' IDENTIFIED BY 'CHANGE_ME_PASSWORD';
GRANT ALL PRIVILEGES ON cloudshopt_orders.* TO 'users'@'%';
FLUSH PRIVILEGES;
```

### Migrations

```
kubectl exec -n cloudshopt -it deploy/order-service -c app -- sh
# php artisan migrate
```
