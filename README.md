# Laravel Api Auth Tests - Step by Step
Pratical step-by-step how to build auth tests in a RESTful API made in Laravel 5.5

### Prerequisites
* Apache
* PHP
* Composer
* [Laravel new app created](https://github.com/cantellir/laravel-new-app)
* [Laravel api auth with passport done](https://github.com/cantellir/laravel-api-auth)

### Initial notes
The project in this repo contains all the steps finalized

### Step 1 - Add seeds to register user for tests
In terminal run
```
php artisan make:seeder UsersTableSeeder
```

Edit 'database/seeds/UsersTableSeeder.php' file
```php
<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */

    public function run()
    {   
        //clear table
        User::truncate();

        User::create([
            'name' => 'username',
            'email' => 'user@email.com',
            'password' => bcrypt('userpass'),
        ]);
    }
}
```

### Step 2 - Call new seeder
Adjust seeds/DatabaseSeeder.php to call users seed
```php
    public function run()
    {
        $this->call(UsersTableSeeder::class);
    }
```

### Step 3 - Configure SQLite for tests
In the config/database.php configure sqlite to work in memory
```
[...]
'connections' => [

    'sqlite' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'prefix' => '',
    ],
    
    ...
]
[...]
```

### Step 4 - Configure phpunit.xml
In the root dir of the project adjust phpunit.xml adding DB_CONNECTION
```
    <php>
        <env name="APP_ENV" value="testing"/>
        <env name="CACHE_DRIVER" value="array"/>
        <env name="SESSION_DRIVER" value="array"/>
        <env name="QUEUE_DRIVER" value="sync"/>
        <env name="DB_CONNECTION" value="sqlite"/>
    </php>
```

### Step 5 - Alter TestCase to prepare project to tests
In the tests/TestCase.php add commands necessary to prepare database for tests
```php
<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        Artisan::call('db:seed');
        Artisan::call('passport:install');        
    }
}

```

### Step 6 - Add script to run tests
In the composer.json, add script to run tests
```
   "scripts": {
        "test" : [
            "vendor/bin/phpunit"
        ]
    ... 
    },  
```

### Step 7 - Generate Feature Test for LoginController
In the terminal run
```
php artisan make:test Auth/LoginControllerTest
```

### Step 8 - Add tests to LoginController
In the tests/Feature/Auth/LoginControllerTest.php add tests
for validations, success login and logout
```php
<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

class LoginControllerTest extends TestCase
{
    public function testRequireEmailAndLogin()
    {
        $this->json('POST', 'api/login')
            ->assertStatus(422)                
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.']
                ]
            ]);
                        
    }

    public function testUserLoginSuccessfully()
    {
        $user = ['email' => 'user@email.com', 'password' => 'userpass'];
        $this->json('POST', 'api/login', $user)
            ->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]
            ]);
    }

    public function testLogoutSuccessfully()
    {
        $user = ['email' => 'user@email.com',
            'password' => 'userpass'
        ];
        
        Auth::attempt($user);
        $token = Auth::user()->createToken('nfce_client')->accessToken;
        $headers = ['Authorization' => "Bearer $token"];
        $this->json('GET', 'api/logout', [], $headers)
            ->assertStatus(204);
    }
}
```

### Step 9 - Generate Feature Test for RegisterController
In the terminal run
```
php artisan make:test Auth/RegisterControllerTest
```

### Step 10 - Add tests to RegisterController
In the tests/Feature/Auth/RegisterControllerTest.php add tests
for validations and success register
```php
class RegisterControllerTest extends TestCase
{    
    public function testRegisterSuccessfully()
    {
        $register = [
            'name' => 'UserTest',
            'email' => 'user@test.com',
            'password' => 'testpass',
            'password_confirmation' => 'testpass'
        ];

        $this->json('POST', 'api/register', $register)
            ->assertStatus(201)
            ->assertJsonStructure([
                'token',
                'user' => [
                    'id',
                    'name',
                    'email',
                    'created_at',
                    'updated_at'
                ]                
            ]);
    }

    public function testRequireNameEmailAndPassword()
    {
        $this->json('POST', 'api/register')
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'name' => ['The name field is required.'],
                    'email' => ['The email field is required.'],
                    'password' => ['The password field is required.'],                
                ]
            ]);
    }

    public function testRequirePasswordConfirmation()
    {
        $register = [
            'name' => 'User',
            'email' => 'user@test.com',
            'password' => 'userpass'
        ];

        $this->json('POST', 'api/register', $register)
            ->assertStatus(422)
            ->assertJson([
                'message' => 'The given data was invalid.',
                'errors' => [
                    'password' => ['The password confirmation does not match.']
                ]
            ]);
    }
}
```

### Step 11 - Run tests
In the terminal run
```
composer test
```

## References
* [Laravel docs](https://laravel.com/docs/5.5) - Laravel Documentation
* [Laravel Passport Post](https://laravelcode.com/post/laravel-passport-create-rest-api-with-authentication) - Create REST API with authentication
* [Laravel API Tutorial](https://www.toptal.com/laravel/restful-laravel-api-tutorial) - How to Build and Test a RESTful API
