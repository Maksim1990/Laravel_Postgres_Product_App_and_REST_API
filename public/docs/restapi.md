# REST API Guide


**Authentication and authorization of this REST API app is based on JWT tokens.**
```
All request (except /login & /register) shold have Authorization header with following value:
Bearer + [space] + [JWT token]
```

### User
1) Register new user
- ROUTE: **/api/register**
- METHOD: **POST**
- Required parameters:
```
name, email, password, password_confirmation
```
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API1.PNG)
2) Login and obtain JWT token
- ROUTE: **/api/login**
- METHOD: **POST**
- Required parameters:
```
email, password
```
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API2.PNG)

### Products
1) Create
- ROUTE: **/api/{user_id}/products/create**
- METHOD: **POST**
- Required parameters:
```
name, case_count, size, brand, file, categories 
```
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API3.PNG)
*Example of incomplete request*
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API4.PNG)
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API5.PNG)
*Example of successfull request*

2) CSV Import
- ROUTE: **/api/{user_id}/products/import**
- METHOD: **POST**
- Required parameters:
```
file
```
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API13.PNG)
3) Update specific product
- ROUTE: **/api/{user_id}/products/{id}**
- METHOD: **POST**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API6.PNG)
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API7.PNG)
4) Get all products
- ROUTE: **/api/{user_id}/products**
- METHOD: **GET**
5) Get specific product
- ROUTE: **/api/{user_id}/products/{id}**
- METHOD: **GET**
5) Delete specific product with related content
- ROUTE: **/api/{user_id}/products/{id}**
- METHOD: **DLETE**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API8.PNG)

### Categories
1) Get all categories
- ROUTE: **/api/{user_id}/categories**
- METHOD: **GET**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API9.PNG)
2) Get specific category
- ROUTE: **/api/{user_id}/categories/{id}**
- METHOD: **GET**
3) Update specific category
- ROUTE: **/api/{user_id}/categories/{id}**
- METHOD: **PATCH**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API10.PNG)
4) Delete specific category
- ROUTE: **/api/{user_id}/categories/{id}**
- METHOD: **DELETE**

### Attachments
1) Get all attachments
- ROUTE: **/api/{user_id}/attachments**
- METHOD: **GET**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API11.PNG)
2) Get specific attachment
- ROUTE: **/api/{user_id}/attachments/{id}**
- METHOD: **GET**
3) Update attachment caption
- ROUTE: **/api/{user_id}/attachments/{id}/caption/update**
- METHOD: **PATCH**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API12.PNG)
4) Delete specific attachment
- ROUTE: **/api/{user_id}/attachments/{id}**
- METHOD: **DELETE**
![Mockup for feature A](https://github.com/Maksim1990/Laravel_Postgres_Product_App_and_REST_API/blob/master/public/example/API/API14.PNG)
