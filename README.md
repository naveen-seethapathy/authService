# AuthService

### **Description**

This will create a dockerized stack for a Laravel/Lumen application, consisted of the following containers:
-  **src/auth**, Lumen application container

        Nginx, PHP7.2 PHP7.2-fpm, Composer
            
-  **mysql**, MySQL database container 

-  **redis**, Redis container

#### **Directory Structure**
```
+-- src/auth <project root>
+-- .gitignore
+-- .env <docker-compose env vars>
+-- docker-compose.yml
+-- readme.md <this file>
```

### **Setup instructions**

**Prerequisites:** 

* Depending on your OS, the appropriate version of Docker Community Edition has to be installed on your machine.  ([Download Docker Community Edition](https://hub.docker.com/search/?type=edition&offering=community))

**Installation steps:** 

1. Clone this repository in to the `/Users/<name>` directory. If cloned in to other directory, make sure to have the correct paths in the `.env` file in the root of the cloned directory.

2. Create two new directories named `mysql` and `redis` inside the cloned directory. This will be used as volume mount location for the services respectively.

3. Copy local .env file to root of lumen app `cp src/auth/env_configs/local/.env src/auth/.env`

4. Open a new terminal/CMD, navigate to this repository root (where `docker-compose.yml` exists) and execute the following command:

    ```
    $ docker-compose up -d
    ```

5. After the whole stack is up, enter the app container and install the framework of your choice:

    ```
    $ docker exec -it auth bash
    $ php artisan migrate --seed
    ```

5. That's it! Navigate to [http://localhost](http://localhost) to access the application.

### **API Documentation**

[Swagger API Documentation](https://app.swaggerhub.com/apis-docs/nseethapathy/authService/1.0.0)

### **Other Documents**

- Technical Roadmap:    `roadmap.pdf`
- System Design:   `Auth-Inventory-Design.pdf`
- Design Specification:    `Design-Specification.pdf`
- Schema:   `user-products-orders-schema.pdf`