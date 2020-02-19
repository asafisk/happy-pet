# happy-pet
HTTP API Game Prototype

Author: Asa Fisk
Date: 2020-02-16


 - - - - - - - - - - - - - - 
| Not for use in Production |
 - - - - - - - - - - - - - -


Summary:

 - Written in PHP, with minimal / no external requirements
  - Using SQLite for data store
  - No reliance on .htaccess or other server config for eg. pretty URLs
  - No dependency management required 
  - No framework used
 - Responses are JSON
 - No authentication in place - just state a User Id in requests
 - Simple dashboard available at application root (/index.php)

Requirements:

 - Requires PHP >= 7.0
 - SQLite PHP extension must be enabled
 - app/data directory must be writable by server


Pet types:

 - 1. Dog - likes being stroked 10 times a day, needs feeding twice a day
 - 2. Cat - likes being stroked 20 times a day, needs feeding once a day 
 - 3. Bird - likes being stroked once a day, needs feeding 10 times a day
 - 4. Snake - likes being stroked once a week, needs feeding once a week
 - 5. Spider - hates being stroked, needs feeding once a month
 


Relative API Endpoints:


/api/?action=register-user
POST
Post Body: 
    name = {user name}
Response: 
    new user id


/api/?user={user_id}&action=register-pet
POST
Post Body:
    pet_name = {pet name}
    pet_type_id = {pet_type_id}
JSON Response:
    new pet id


/api/?user={user_id}&pet_id={pet_id}&action=feed
POST
Post Body:
    null
JSON Response:
    new event id


/api/?user={user_id}&pet_id={pet_id}&action=stroke
POST
Post Body:
    null
JSON Response:
    new event id


/api/?user={user_id}&pet_id={pet_id}&action=status
GET
JSON Response:
    Status description resulting from feeding and stroking activity

api/?user={user_id}&action=listpets
GET
JSON Response:
    List of that user's pets


