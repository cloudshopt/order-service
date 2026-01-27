# Order service

## Create order database + user on mysql server
```
kubectl -n cloudshopt exec -it cloudshopt-mysql-0 -- bash

mysql -u root -prootpass
```

```
CREATE DATABASE cloudshopt_orders CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'users'@'%' IDENTIFIED BY 'userspass';
GRANT ALL PRIVILEGES ON cloudshopt_orders.* TO 'users'@'%';
FLUSH PRIVILEGES;
```

Ustvari še bazo za *dev* okolje
```
CREATE DATABASE cloudshopt_orders_dev CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'users_dev'@'%' IDENTIFIED BY 'userspass';
GRANT ALL PRIVILEGES ON cloudshopt_orders_dev.* TO 'users_dev'@'%';
FLUSH PRIVILEGES;
```

## Crete external secrets for prod and dev
prod:
```
kubectl -n cloudshopt create secret generic order-service-secrets \
  --from-literal=DB_PASSWORD="userspass" \
  --from-literal=REDIS_PASSWORD="redispass" \
  --dry-run=client -o yaml | kubectl apply -f -
```

dev:
```
kubectl -n cloudshopt-dev create secret generic order-service-secrets \
  --from-literal=DB_PASSWORD="userspass" \
  --from-literal=REDIS_PASSWORD="redispass" \
  --dry-run=client -o yaml | kubectl apply -f -
```

check for secrets:
```
kubectl get secret -n cloudshopt order-service-secrets
kubectl get secret -n cloudshopt-dev order-service-secrets
```

## Install order-service for prod and dev
prod:
```
helm upgrade --install order-service ./helm/order-service \
-n cloudshopt \ 
-f helm/order-service/values.yaml
```

dev:
```
helm upgrade --install order-service-dev ./helm/order-service \
-n cloudshopt-dev \ 
-f helm/order-service/values-dev.yaml
```



## Migrations

run migrations:
```
kubectl exec -n cloudshopt-dev -it deploy/order-service-dev -c app -- sh

# php artisan migrate
```

s