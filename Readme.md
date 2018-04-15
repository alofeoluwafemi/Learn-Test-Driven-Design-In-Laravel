#### Overview
This post is aimed at introducing Unit and Functional Testing in Laravel Framework, configuring and setup of test environment, best practices,  dealing with Laravel default exception handling behaviour. We would be creating a CRUD for a simple blog then write test for 

#### What Will I Learn?
- Configure and  write unit test in Laravel Framework
- Using Laravel factory to get fake data
- Disable exception handling while running test
- Test Driven Design

#### Requirements
- Basic knowledge of Laravel framework is required.
- For this tutorial we would be using Laravel version 5.5 and it requires the following to be installed on your machine:
	-   PHP >= 7.0.0
	-   OpenSSL PHP Extension
	-   PDO PHP Extension
	-   Mbstring PHP Extension
	-   Tokenizer PHP Extension
	-   XML PHP Extension

- You must also have composer installed, you can find out how to install composer for your machine [here](https://getcomposer.org/doc/00-intro.md).

#### Difficulty
Basic

#### Setup
This setup assumes you are using a Linux machine. By now you must have had PHP with required dependecies & composer installed.

To install Laravel version 5.5, navigate to you web directory. In my case `/var/www/html` and run command ` composer create-project laravel/laravel practical-testing "5.5.*" --prefer-dist` where `practical-testing` is your project name, you can change that to whatever you want. 

Now Laravel project should be completely setup, navigate into the project directory in your terminal and run `php artisan --version` to see the currently installed Laravel version is `5.5.4`.

#### Wait What Is TDD(Test Driven Design)
Using Wikipedia Definition:

**Test-driven development** (**TDD**) is a [software development process](https://en.wikipedia.org/wiki/Software_development_process "Software development process") that relies on the repetition of a very short development cycle: requirements are turned into very specific [test cases](https://en.wikipedia.org/wiki/Test_case "Test case"), then the software is improved to pass the new tests, only.

##### Life Cycle Of TDD
The life cycle of writing a TDD code which we would also be following includes

 1. Add a test
 2. Run all tests and see if the new test fail
 3. Write the code
 4. Run Tests
 5. Refactor Code

#### Building & Testing Our Blog
Open the project in your prefered editor, edit the `phpunit.xml` in the root directory and on line `25` add the following snippet

```php
<env name="DB_CONNECTION" value="sqlite"/>  
<env name="DB_DATABASE" value=":memory:"/>
```
We are setting the the database to sqlite and save the data to memory when we are in test environment. Test that read or write to the database runs faster and after each test the data is not persistence.

Next create a database with name `practical_testing` any mysql client of your choice, phpmyadmin or similar will do just fine. 

Update your `.env` file with your `DB_DATABASE` , `DB_USERNAME` and `DB_PASSWORD`. Now run `php artisan migrate` to create the migration table and the user table. If you open `database/factories/UserFactory.php` file in your editor you should see the model factory for User Model Defined there by default as we proceed into the tutorial we would add one more for the Blog Model, but now open your terminal and run

```php
php artisan tinker

//You should be presented with a response similar to this
Psy Shell v0.8.18 (PHP 7.***** — cli) by Justin Hileman
```

If you have not tried using laravel tinker before, what this allows us to do is run php codes on the terminal to interact with our laravel project. What we want to achieve here is to create three fake users using our defined user model. Now run the following commands from terminal

```php
//First Code
//To set default namespace to App. 
//This way we can do User instead of App\User
namespace App

//Second Code
factory(User::class,3)->create()
```
Now if you run `User::all()` you should get a list of all three users. this is basically how we would be interacting with the blog to avoid creating any UI. Now create our blog table, run `php artisan make:migration create_blogs_table --create=blogs` 

Update the up method in the migration file with the below schema
```php
Schema::create('blogs', function (Blueprint $table) {  
  $table->increments('id');  
  $table->unsignedInteger('user_id')->references('id')->on('users');  
  $table->string('title');  
  $table->longText('body');  
  $table->timestamps();  
});
```
Run `php artisan migrate` to create table.

**Important Note:** As we procced into this tutoral if running `phpunit` gives you issue, you can instead run `vendor/bin/phpunit` or better still create an alias `punit` for `vendor/bin/phpunit`, that way you can always run `punit` on every of you php project. 

#### Lets Get Testing

First we test if we can successfully create a blog post. Remember lifecycle of TTD expects us to write our test to fail before we write the code and make the test pass. 
Run command `php artisan make:test  CreatePostTest` to create a test file. Check the `tests/Feature` folder the newly created test file would be there, next we add our first test

```php
/**  
 * @test  
  */  
public function a_logged_user_can_create_blog_post()  
{  
  $response = $this->get('post/create');  
  
  $response->assertSuccessful();  
}
```
If you run the test by executing `phpunit` from the terminal, it will fail with error 
```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: 132 ms, Memory: 10.00MB

There was 1 failure:

1) Tests\Feature\CreatePostTest::a_logged_user_can_create_blog_post
Response status code [404] is not a successful status code.
Failed asserting that false is true.

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/TestResponse.php:60
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:18

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.

```

because the route to create a post does not exist yet, now add this line to web.php to create the route
```php
Route::get('/post/create','PostController@create');
```
 then run the test again, now the test will fail again with error 

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

F                                                                   1 / 1 (100%)

Time: 119 ms, Memory: 14.00MB

There was 1 failure:

1) Tests\Feature\CreatePostTest::a_logged_user_can_create_blog_post
Response status code [500] is not a successful status code.
Failed asserting that false is true.

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/TestResponse.php:60
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:18

FAILURES!
Tests: 1, Assertions: 1, Failures: 1.

```

But wait a minute from the fail test above we can't debug exactly what is causing the test to fail, except for the fact that we know we don't have a PostController yet. This is due to the way laravel handles exception, we can simply fix that by replacing the `/tests/TestCase.php` with 

```php
<?php  
  
namespace Tests;  
  
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;  
use Illuminate\Contracts\Debug\ExceptionHandler;  
use Illuminate\Foundation\Exceptions\Handler;  
  
abstract class TestCase extends BaseTestCase  
{  
  use CreatesApplication;  
  
  protected function setUp()  
  {  
    parent::setUp();  
    $this->disableExceptionHandling();    
  }  
 
  protected function disableExceptionHandling()  
  {  
  $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);  
  $this->app->instance(ExceptionHandler::class, new class extends Handler {  
  public function __construct() {}  
  public function report(\Exception $e) {}  
  public function render($request, \Exception $e) {  
  throw $e;  
   } 
  }); 
 }
    
  protected function withExceptionHandling()  
  {  
  $this->app->instance(ExceptionHandler::class, $this->oldExceptionHandler);  
  return $this;  
  }
 }
``` 
Credit of the above code goes to [Adam Watham](https://github.com/adamwathan)

The code above ensures that before any test is executed replaces the default exception handler and instead show the error log. If you which to use the default exception handling behaviour instead just call `$this->withExceptionHandling()` in your test.

Now if you run `phpunit` you will presented with a detailed error log 

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: 86 ms, Memory: 10.00MB

There was 1 error:

1) Tests\Feature\CreatePostTest::a_logged_user_can_create_blog_post
ReflectionException: Class App\Http\Controllers\PostController does not exist

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Container/Container.php:752
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Container/Container.php:631
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Container/Container.php:586
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Application.php:732
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Route.php:226
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Route.php:796
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Route.php:757
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:671
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:651
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:635
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:601
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:590
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:176
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:30
/var/www/html/practical-testing/vendor/fideloper/proxy/src/TrustProxies.php:56
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:30
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:30
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ValidatePostSize.php:27
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php:46
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:102
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:151
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:116
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php:345
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php:168
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:16

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.

```
Now we are armed with a powerful way to debug our test.

Create a `PostController` by running command `php artisan make:controller PostController` then open the file and update it with the create method

```php
public function create()  
{  
  return view('welcome');  
}
```
The `welcome.blade.php` already exist when you installed laravel, now run `phpunit` once again. Good our test will pass now

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

.                                                                   1 / 1 (100%)

Time: 242 ms, Memory: 10.00MB

OK (1 test, 1 assertion)
```
Now lets write test that fails to see if can post data to this url and the data is saved into database. But first we need to create Blog model and factory, run `php artisan make:model Blog` and add below snippet at the end of `database/factories/UserFactory.php`

```php
$factory->define(\App\Blog::class,function (Faker $faker) {  
  return [  
  'user_id' => function(){ return (factory(\App\User::class)->create())->id; },  
  'title' => $faker->sentence(),  
  'body' => $faker->sentence(100),  
 ];});
```
Now update your test code with this

```php
/**  
 * @test  
  */  
public function a_logged_user_can_create_blog_post()  
{  
  $response = $this->get('post/create');  
  
  $response->assertSuccessful();  
  
  //Note the use of make() & not create()
  //difference is make does not persist data
  //into database unlike create
  $data = (factory(Blog::class)->make())->toArray();  
  
  $this->post('post/create',$data);  
  
  $this->assertDatabaseHas('blogs',$data);
}
```
What we have done right now is write a test on how our code should behave, run `phpunit`
```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: 82 ms, Memory: 14.00MB

There was 1 error:

1) Tests\Feature\CreatePostTest::a_logged_user_can_create_blog_post
Illuminate\Database\QueryException: SQLSTATE[HY000]: General error: 1 no such table: users (SQL: insert into "users" ("name", "email", "password", "remember_token", "updated_at", "created_at") values (Dennis Quitzon, shaylee.botsford@example.org, $2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm, pAixCXGUKO, 2018-04-15 10:57:21, 2018-04-15 10:57:21))

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:664
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:624
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:459
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:411
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Query/Processors/Processor.php:32
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:2159
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1285
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:722
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:687
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:550
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:172
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Support/Collection.php:339
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:173
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:151
/var/www/html/practical-testing/database/factories/UserFactory.php:27
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:317
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:232
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:252
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/GuardsAttributes.php:122
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:260
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:185
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:21

Caused by
PDOException: SQLSTATE[HY000]: General error: 1 no such table: users

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:452
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:657
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:624
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:459
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Connection.php:411
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Query/Processors/Processor.php:32
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Query/Builder.php:2159
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1285
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:722
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:687
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:550
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:172
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Support/Collection.php:339
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:173
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:151
/var/www/html/practical-testing/database/factories/UserFactory.php:27
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:317
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:232
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:252
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Concerns/GuardsAttributes.php:122
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:260
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/FactoryBuilder.php:185
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:21

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
```
Remember we configured phpunit to use sqlite, so everytime we run our test no data is persisted. Laravel provides a trait  `DatabaseMigrations` that runs migration everytime you run a test. Add this statement to the top the file `use Illuminate\Foundation\Testing\DatabaseMigrations;` 

Your test file should look exactly like this now

```php
<?php  
  
namespace Tests\Feature;  
  
use App\Blog;  
use Tests\TestCase;  
use Illuminate\Foundation\Testing\WithFaker;  
use Illuminate\Foundation\Testing\RefreshDatabase;  
use Illuminate\Foundation\Testing\DatabaseMigrations;  
  
class CreatePostTest extends TestCase  
{  
  use DatabaseMigrations;  
  
 /**  
 * @test  
 */  
 public function a_logged_user_can_create_blog_post()  
 {  
  $response = $this->get('post/create');  
  
  $response->assertSuccessful();  
  
  $data = (factory(Blog::class)->make())->toArray();  
  
  $this->post('post/create',$data);  
  
  $this->assertDatabaseHas('blogs',$data);  
 }}
```

run `phpunit`, the test will fail with a new error

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: 298 ms, Memory: 16.00MB

There was 1 error:

1) Tests\Feature\CreatePostTest::a_logged_user_can_create_blog_post
Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException: 

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/RouteCollection.php:255
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/RouteCollection.php:242
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/RouteCollection.php:176
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:612
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:601
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:590
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:176
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:30
/var/www/html/practical-testing/vendor/fideloper/proxy/src/TrustProxies.php:56
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:30
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:30
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ValidatePostSize.php:27
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php:46
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:102
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:151
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:116
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php:345
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php:195
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:26

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
```
The test failed because we have not defined our post route. Lets create the route and the controller method to save the post, also in-between create a unit test to check the method that would handle how it save the data from the model. 

Add this line to your `web.php` file 

```php
Route::post('/post/create','PostController@store');
```

Also this method to your PostController

```php
public function store(Request $request,Blog $blog)  
{  
  $blog->addPost($request->all());  
  
  return response(200);  
}
```

Notice a method `addPost` that does not already exist on the Blog Model. Now we to write a unit test to create that method. run `php artisan make:test NewPostTest --unit`

Navigate to the Unit folder in your test folder open `NewPostTest.php` file and paste this in there

```php
<?php  
  
namespace Tests\Unit;  
  
use App\Blog;  
use Tests\TestCase;  
use Illuminate\Foundation\Testing\WithFaker;  
use Illuminate\Foundation\Testing\RefreshDatabase;  
use Illuminate\Foundation\Testing\DatabaseMigrations;  
  
class NewPostTest extends TestCase  
{  
  use DatabaseMigrations;  
  
  /**  
 * @test  
  */  
  public function successfully_add_new_post()  
 {  $post = (factory(Blog::class)->make())->toArray();  
  
 (new Blog())->addNewPost($post);  
  
  $this->assertDatabaseHas('blogs',$post);  
 }}
```

run `phpunit --filter successfully_add_new_post` to only execute this single test and not all our tests. As expected this test would fail because we don't have the method `addNewPost` yet on our model, understand that this test is to specifically unit test the method `addNewPost` unlike the previous test that is aimed at testing the a feature. Now add the method to Blog model

```php
public function addNewPost($data)  
{  
  return $this->create($data);  
}
```

Again run `phpunit --filter successfully_add_new_post`, we would now get an error for mass assignment

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: 131 ms, Memory: 14.00MB

There was 1 error:

1) Tests\Unit\NewPostTest::successfully_add_new_post
Illuminate\Database\Eloquent\MassAssignmentException: user_id

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:232
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:152
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:290
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:1068
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Builder.php:756
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Database/Eloquent/Model.php:1470
/var/www/html/practical-testing/app/Blog.php:11
/var/www/html/practical-testing/tests/Unit/NewPostTest.php:22

ERRORS!
Tests: 1, Assertions: 0, Errors: 1.
```
To solve this issue we just have to allow mass assignment for all the fields we always pass on blogs table. Add the below line to your Blog Model

```php
protected $fillable = ['user_id','title','body'];
```

Now if you run `phpunit --filter successfully_add_new_post` the test will pass now. We can now go back to our previous test, now if you run `phpunit --filter a_logged_user_can_create_blog_post`, our test will pass successfully but it staill doesn't satisfy its purpose because we want only logged users to be able to create post. Add middleware `auth` to the post route
```php
Route::post('/post/create','PostController@store')->middleware('auth');
```
If you run the test again it will fail with UnAuthenticated error.

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

E                                                                   1 / 1 (100%)

Time: 154 ms, Memory: 16.00MB

There was 1 error:

1) Tests\Feature\CreatePostTest::a_logged_user_can_create_blog_post
Illuminate\Auth\AuthenticationException: Unauthenticated.

/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Auth/GuardHelpers.php:40
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Auth/AuthManager.php:292
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:57
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Auth/Middleware/Authenticate.php:41
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/VerifyCsrfToken.php:67
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/View/Middleware/ShareErrorsFromSession.php:49
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Session/Middleware/StartSession.php:63
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Cookie/Middleware/AddQueuedCookiesToResponse.php:37
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Cookie/Middleware/EncryptCookies.php:59
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:102
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:660
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:635
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:601
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Router.php:590
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:176
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:30
/var/www/html/practical-testing/vendor/fideloper/proxy/src/TrustProxies.php:56
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:30
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/TransformsRequest.php:30
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/ValidatePostSize.php:27
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Middleware/CheckForMaintenanceMode.php:46
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:149
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Routing/Pipeline.php:53
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Pipeline/Pipeline.php:102
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:151
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Http/Kernel.php:116
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php:345
/var/www/html/practical-testing/vendor/laravel/framework/src/Illuminate/Foundation/Testing/Concerns/MakesHttpRequests.php:195
/var/www/html/practical-testing/tests/Feature/CreatePostTest.php:26

ERRORS!
Tests: 1, Assertions: 1, Errors: 1.
```
So simply update your test to fix it as follows

```php
/**  
* @test  
*/  
public function a_logged_user_can_create_blog_post()  
{  
  $response = $this->get('post/create');  
  
  $response->assertSuccessful();  
  
  $user = factory(User::class)->create();  
  
  $this->actingAs($user);  
  $data = (factory(Blog::class)->make(['user_id' => $user->id]))->toArray();  
  
  $this->post('post/create',$data);  
  
  $this->assertDatabaseHas('blogs',$data);  
}
```
Run `phpunit` with no filter both test will pass successfully

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

..                                                                  2 / 2 (100%)

Time: 146 ms, Memory: 16.00MB

OK (2 tests, 3 assertions)
```
Lets just create two more test to delete a blog post. In this case we just start with the unit test, run commad `php artisan make:test DeletePostTest --unit` then update its content with

```php
<?php  
  
namespace Tests\Unit;  
  
use App\Blog;  
use Illuminate\Foundation\Testing\DatabaseMigrations;  
use Tests\TestCase;  
use Illuminate\Foundation\Testing\WithFaker;  
use Illuminate\Foundation\Testing\RefreshDatabase;  
  
class DeletePostTest extends TestCase  
{  
  use DatabaseMigrations;  
  
	/**  
	* @test  
	*/  
	public function successfully_delete_post()  
	{  
	  factory(Blog::class,5)->create();  
	  
	  $model = new Blog();  
	  
	  $deleted = $model->find(1)->deletePost();  
  
	  $this->assertTrue($deleted);
	}
}
```

If you run command `phpunit --filter successfully_delete_post` the test will fail because we don't have the method deletePost yet on the Blog Model. Update the Blog Model with the `deletePost` method.

```php
public function deletePost()  
{  
  return $this->delete();  
}
```

Now we can go ahead to write feature test, run `php artisan make:test UserDeletePostTest`. Open the test file add let write the minimum test that should fail

```php
<?php  
  
namespace Tests\Feature;  
  
use App\Blog;  
use Illuminate\Foundation\Testing\DatabaseMigrations;  
use Tests\TestCase;  
use Illuminate\Foundation\Testing\WithFaker;  
use Illuminate\Foundation\Testing\RefreshDatabase;  
  
class UserDeletePostTest extends TestCase  
{  
  use DatabaseMigrations;  
  
 /**  
 * @test  
 */  
public function an_authenticated_user_can_successfully_delete_its_own_post()  
{  
  $this->actingAs(factory(User::class)->create());  
  
  factory(Blog::class,5)->create();  
  
  $this->delete("post/delete/3");  
  
  $this->assertEquals(Blog::count(),4);  
}
```
This test will fail because we do not have our route to delete post in place neither is the controller method. Now lets take care of both

Update route

```php
Route::delete('/post/delete/{blog}','PostController@destroy')->middleware('auth');
```
Update your controller with `destroy` method

```php
public function destroy(Blog $blog)  
{  
  $blog->deletePost();  
  
  return response(200);  
}
```
Now run `phpunit` all 4 tests will pass now

```php
PHPUnit 6.5.8 by Sebastian Bergmann and contributors.

....                                                                4 / 4 (100%)

Time: 136 ms, Memory: 16.00MB

OK (4 tests, 5 assertions)
```
You can clone the repository containing code to this tutorial from [here](https://github.com/slim12kg/Test-Driven-Design-In-Laravel).

Happy TDD