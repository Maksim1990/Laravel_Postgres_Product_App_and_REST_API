# Requirements that covers this application

###### 1) Limit for file uploads:
- For audio: 20 MB
- For video: 50 MB
###### 2) Supported file formats that could be uploaded: 
**jpg, jpeg, png, mp4**
###### 3) At least **one** file should be attached when create/update product
###### 4) Maximum allowed attachments per product: 
**9**
###### 5) At least **one** category should be added when create/update product
###### 6) Maximim 2nd-level categories are allowed. 
```
Example: 1st level:  Samsung
Example: 2nd level:  Samsung:Phone
```
**3rd level categories are not allowed and they will be conerted to stand alone separate category.**
###### 7) Each video or image can have caption. 
- In web interface caption can be easily updated in the modal window when click on specific attachment
- In REST API interface caption can be updated by sending **PATCH** request example_domain.com/ **/api/{user_id}/attachments/{id}/caption/update**
###### 8) Each product after successful creation process should obtain *UPC (Barcode)*
- Barcode is regenrated after any updates that are made to this product **(except changes that are made to linked attachments or categories)**
###### 9) After uploading video or image resource for each item automatically generated thumbnail image with fixed size **400x400** that later will be used for attachment preview.
*Thumbnail images won't be generated for remote attachment that are imported from CSV file.*
###### 10) Height of uploading image should be more than 100px 
