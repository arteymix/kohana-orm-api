#kohana-orm-api

## Synopsis

This module allows kohana ORMs to be fetched and manipulated in javascript/AJAX.

This is a [RESTful web API](https://en.wikipedia.org/wiki/REST#RESTful_web_APIs), where the four HTTP methods GET, POST, PUT and DELETE are used (respectively) to Find, Create, Update and Delete ORM models.

## Configuration

### Configuring the models

Simply implement in your custom class the interface `ORM_Api`. This interface has 3 public methods returning arrays : 

1.  `api_columns()`
2.  `api_expected()`
3.  `api_methods()`

#### `api_columns()`

This method defines the columns that can be accessed through the api for a model. You could, for instance, allow a `Model_User` class to reveal through the api the usernames and ages of your users, but not their primary keys, their password hash, or anything else by using the following code :

    public function api_columns() {
         return array(
             'username',
             'age',
         );
    }

If all the columns in your model are completely public, you can use the following :

    /**
     * All columns are accessible through the API
     */
    public function api_columns() {
        return NULL;
    }

If you want no column to be public, you should return an empty array.

#### `api_expected()`

This method defines what columns are expected when performing an update/create operation.

#### `api_methods()`

This method defines what ORM methods can be accessed through the API. The following methods are available :

1.  `find()`
2.  `find_all()`
3.  `update()`
4.  `create()`
5.  `delete()`

### Routes

The default routes are defined in init.php, but can be overidden thanks to Kohana's cascading filesystem. The default values are :

*   `api/<model>(/<id>)`
*   `api/count/<model>` for the `->count_all()` helper

## Usage/Examples

Note : since doing cross-browser compatible AJAX is an horrible task to do and since nobody does that anymore, we'll just assume in the following examples that you are using jQuery. It's a free (as in freedom) library that simplifies AJAX requests, along with every other thing raw javascript does and there is no excuse not to use it.

> You are going to put jQuery on your website, and that's final, young sir !

All complaints about that fact should be sent directly to /dev/null.



## Security

As the GNU licences say so well :

> This software is distributed in the hope that it will be useful,
> but WITHOUT ANY WARRANTY; without even the implied warranty of
> MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

In a few words, we believe that this module is safe to use (when correctly configured), due to the simplicity of it's code, but be aware that we are humans and mistakes can always happen.

## In Conclusion

> And if you don't like the way we code, you can go fork yourself !
