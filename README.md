# API de Reserva de Espacios

Este proyecto es una API construida con Laravel 10, que proporciona funcionalidades para gestionar usuarios, roles, y lugares. Utiliza varios paquetes para mejorar su funcionalidad y seguridad.

## Requisitos

- PHP: ^8.1
- Composer
- Laravel: ^10.10

## Paquetes Utilizados

La API utiliza los siguientes paquetes:

- **Laravel Framework**: ^10.10
- **Darkaonline L5 Swagger**: ^8.6 - Para la generación de documentación de la API.
- **Spatie Laravel Permission**: ^6.9 - Gestión de roles y permisos.
- **Tymon JWT Auth**: ^2.1 - Autenticación basada en JWT.

## Instalación

1. Clona el repositorio:
  
   git clone https://github.com/rebecam24/bookings.git

2. Navega al directorio del proyecto:

   cd bookings


3. Instala las dependencias:
  
   composer install
 

4. Copia el archivo de configuración de ejemplo:

   cp .env.example .env


5. Configura tu archivo `.env` con las credenciales de tu base de datos y otras configuraciones necesarias.

6. Genera la clave de aplicación:

   php artisan key:generate

7. Ejecuta las migraciones y los seeders:
 
   php artisan migrate --seed

## Migraciones y Seeders

Este proyecto incluye migraciones para crear las tablas necesarias en la base de datos. También se incluyen seeders para poblar las tablas de **places**, **users** con un usuario de tipo administrado,  y **roles** los roles seran 'user' y 'admin'.

- **Migraciones**: Las migraciones se encuentran en la carpeta `database/migrations`.
- **Seeders**: Los seeders se encuentran en la carpeta `database/seeders`. Puedes ejecutar el seeder específico para poblar los datos.

## Documentación de la API

La documentación de la API se genera automáticamente utilizando el paquete **Darkaonline L5 Swagger**. Puedes acceder a ella en la siguiente ruta:

http://localhost:8000/api/documentation


Asegúrate de que el servidor esté en funcionamiento antes de acceder a esta URL.

## Autenticación

La API utiliza **JWT** para la autenticación. Asegúrate de incluir el token de autenticación en las cabeceras de tus solicitudes.
