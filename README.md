#kohana-orm-api

## Synopsis

This module provides an api for fetching and manipulating ORM models over a [RESTful web API](https://en.wikipedia.org/wiki/REST#RESTful_web_APIs). The four HTTP methods GET, POST, PUT and DELETE are used (respectively) to Find, Create, Update and Delete ORM models.

## Routing

Models are fetched following this syntax

    api/<model>(/<id>(/<action>))
    
If <model> is plural, a list of models will be returned in the JSON-encoded response body. The suffix \_all will be appended to the called method. (eg. find => find\_all)

As a default behiavior, the HTTP method is used to resolve the method to call on a model. Everything is therefore handled in the index action.

    GET is mapped to find
    PUT is mapped to create
    POST is mapped to update
    DELETE is mapped to delete

<action> is used to perform other operations such as has, add or remove.

Implemented actions are

### count

count will return a JSON-encoded integer.

### has, add and remove

A JSON object must be providen, containing

    {
        'alias': <alias>,
        'far_keys': <far_keys>
    }

add and remove will return an empty body with a 200 status code on success.

has will return a JSON-encoded boolean.

## Configuration

The only configuration required is a policy file that defines what calls are authorized on your models.

    return array(
        <model> => array(
            <method> => array(
                'columns' => array(),  // columns exposed to manipulation (sorting, filtering, ...)
                'expected' => array(), // columns exposed to modification
            )
        )
    );

For example, if you want to expose registered usernames

    return array(
        'User' => array(
            'find' => array(
                'columns' => array('username'),
            )
        )
    );
    
Use an empty array to signify a complete negation and the NULL value to allow anything

    return array(
        'User' => array(
            'find' => array(
                'columns' => array() // no columns exposed
                'expected' => NULL   // any value can be modified
            )
        )
    );
    
## Usage/Examples

jQuery is well suited for the job when it comes to api-based website. You can create a nice app using its ajax implementation.

Note : since doing cross-browser compatible AJAX is an horrible task to do and since nobody does that anymore, we'll just assume in the following examples that you are using jQuery. It's a free (as in freedom) library that simplifies AJAX requests, along with every other thing raw javascript does and there is no excuse not to use it.

> You are going to put jQuery on your website, and that's final, young sir !

All complaints about that fact should be sent directly to /dev/null.


## Security

As the GNU licences say so well :

> This software is distributed in the hope that it will be useful,
> but WITHOUT ANY WARRANTY; without even the implied warranty of
> MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

This is an api that exposes your model in a generic manner, so do not take security concers with a grain of salt.

In a few words, we believe that this module is safe to use (when correctly configured), due to the simplicity of it's code, but be aware that we are humans and mistakes can always happen. Still, since this is a free project, you can always double-check the code yourself (or pay a developer to do it).

## License

Not decided yet. Comming soon :)

## In Conclusion

> And if you don't like the way we code, you can go fork yourself !
