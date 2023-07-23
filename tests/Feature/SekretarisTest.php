<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SekretarisTest extends TestCase
{
   /**
     * A basic feature test example.
     *
     * @return void
     */
    public function auth()
    {
        auth()->attempt([
            'username'=>'sekretaris',
            'password'=>'password'
        ]);
        
        return auth()->user();
    }

    //check home route
    public function test_home_route()
    {   
        $user = $this->auth();
        $response = $this->actingAs($user)->get('/');
        $response->assertSuccessful();
    }

    //check local-storage.index route
    public function test_local_storage_index_route()
    {   
        $user = $this->auth();
        $response = $this->actingAs($user)->get('/local-storage');
        //check if dont have access
        $response->assertForbidden();
    }

    //check cloud-storage.index route
    public function test_cloud_storage_index_route()
    {   
        $user = $this->auth();
        $response = $this->actingAs($user)->get('/cloud-storage');
        $response->assertForbidden();
    }

    //check role.index route
    public function test_role_index_route()
    {   
        $user = $this->auth();
        $response = $this->actingAs($user)->get('/role');
        $response->assertForbidden();
    }

    //check user.index
    public function test_user_index_route()
    {   
        $user = $this->auth();
        $response = $this->actingAs($user)->get('/user');
        $response->assertForbidden();
    }

    //check surat.index
    public function test_surat_index_route()
    {   
        $user = $this->auth();
        $response = $this->actingAs($user)->get('/surat');
        $response->assertSuccessful();
    }
}
