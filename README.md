#Sync

This is a pseudo-code representation of how I would handle OAuth and API calls to different external providers, but unify and sync the data to the locally agreed upon standards.

----

Here is a brief rundown of the files that are included:

- Connectors
    - ApiConsumerException - Just a standard exception class
    - ApiConsumerInterface - An interface all ApiConsumers should use with 4 main methods
    - BaseApiConsumer - An abstract class that ApiConsumers should extend. Contains the base Guzzle request to make api calls using a token based authentication system. Requires that child classes write methods to interact with the base request
    - CourseInterface - All Api Courses should implement this interface. Has a method to convert to the internal standard Class Entity
    - StudentInterface - All Api Students should implement this interface. Has a method to convert to the internal standard User Entity
    
- Entities
    - ClassEntity - The standard/mapped entity that is returned when fetching classes from the local database.
    - IntegrationEntity - A utility class that provides constants related to different integration types
    - UserEntity - The standard/mapped entity that is returned when fetching users from the local database.
    
- Integrations
    - Blackboard/Canvas
        - Entities - These files contain the structure of data that is returned from the external APIs. The data properties can be vastly different, so each contains a conversion method to convert it to a Class or User Entity
        - ApiConsumer - These files contain the different methodologies for fetching data from the remote APIs. Every API handles things like pagination, limits, query parameters, and returned data formats differently, so there needs to be some flexibility. ApiConsumer extends the BaseApiConsumer and implements the ApiConsumerInterface.
        - OAuthHandler - Most OAuth systems are going to be more or less the same. As luck would have it, both Canvas and Blackboard are just standard OAuth systems and no special parameters need to be sent. This class extending the OAuthHandler parent class allows for flexibility for individual authentication systems.
    
- OAuth
    - OAuthException - Just a standard exception class
    - OAuthHandler - A functional implementation using Guzzle of connecting to an OAuth server and exchanging a consumer id and secret for a token.

- Repositories
    - ClassRepository - This is an example of a database repository that would fetch data from a local database and return that data as the standardized entities.
    - DatabaseRepository - The parent class that has some dummy methods
    - UserRepository - Same as the class repository. Just an example of how to perform CRUD operations on a local database
    
- SyncHandler - This is where the magic happens. This takes in an ApiConsumerInterface and will execute the standard/needed API calls and convert the data using the methods in each of the individual provider entities, and then use the standardized data to determine what needs to be created, updated, and deleted.

An Example.php file was provided that shows how this code could potentially be implemented in a controller. There is a route that will handle the OAuth process and store the OAuth token to cache. There is an example route that will fetch remote api classes. And finally a route that will handle the sync process based on user input requesting a sync of several classes.

----

The major benefits to a model/pattern like this is that minimal code would need to be written for each provider. There definitely could be duplicated code, but a lot of the standard boilerplate code for making API requests is abstracted out. There is also the requirement that all API entities have the ability to convert the data to internal entities. This is going to be different for **every** api, so a method/interface that provides a unified standard will allow a single "Sync" class or other handler to manipulate data from ANY api providing that it is implementing the correct interface and method.

----

Obviously, things would be laid out a bit differently in an actual application. Laravel uses Models instead of Entities, and Eloquent or the DB connector would handle the actual querying of data. I would also be leveraging environment variables and config files for things like the oauth consumer ids and secrets.
    
