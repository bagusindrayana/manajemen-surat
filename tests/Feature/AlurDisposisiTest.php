<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AlurDisposisiTest extends TestCase
{
    //test admin menambah surat
    public function test_admin_menambah_surat()
    {   
        $user = auth()->attempt([
            'username'=>'admin',
            'password'=>'password'
        ]);;
        $response = $this->actingAs($user)->post('/surat', [
            'nomor_surat' => 'surat-test-'.\Str::random(10),
            'tanggal_surat' => date("Y-m-d"),
            'perihal' => 'test',
            'sifat'=> 'biasa',
            'isi' => 'test',
            'keterangan' => 'test',
            'pemeriksa_id' => '3',
        ]);
        $response->assertRedirect('/surat');
    }

    //test lurah melihat surat dengan nomor yang memiliki 'surat-test-'
    public function test_lurah_melihat_surat()
    {   
        $user = auth()->attempt([
            'username'=>'lurah',
            'password'=>'password'
        ]);;
        $response = $this->actingAs($user)
        ->get('/surat?search=surat-test-')
        ->seeInElement('tbody.tr');
        $response->assertSuccessful();
    }
}
