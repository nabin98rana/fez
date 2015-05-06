# REST API: Fez Generic

## Resource URI

    https://espace.library.uq.edu.au/

## Resource Properties

Fez has a generic API where you can get and push basic data

You can chose json or xml ( format=json format=xml ) through get or post.

Generally you can access this if there is a .xml template in -

https://github.com/uqlibrary/fez/tree/master/public/templates/en

Currently this is, list (ie search), view, view_metadata, edit_security, enter_metadata, edit_metadata.

You must first authorise through Basic Auth. 

Using Postman from the Chrome Web store you can quick test using a link like http://espace.library.uq.edu.au/list/?cat=quick_filter&sort_by=searchKey0&search_keys%5B0%5D=&format=json and setting the Basic Auth tab in Postman to your login.