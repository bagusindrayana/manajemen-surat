<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function test_redirect()
    {
        $response = $this->get('/');

        $response->assertRedirect('/login');
    }

    //login route
    public function test_login_route()
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    //login form
    public function test_login_form()
    {
        $response = $this->post('/login',[
            'username'=>'admin',
            'password'=>'password'
        ]);
        $response->assertRedirect('/');
    }
}
