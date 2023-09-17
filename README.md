## Aplikasi Disposisi & Manajemen Surat Masuk
aplikasi ini memanfaatkan integrasi multi cloud storage, artinya kita dapat menghubungkan banyak cloud storage sekaligus, dalam hal ini kita menggunakan google drive.

### Requirement
- PHP >= 8.0.2

### Development
- Clone repository ini
- jalankan perintah `composer install`
- copy file `.env.example` menjadi `.env`
- jalankan perintah `php artisan key:generate`
- isi konfigurasi database di file `.env`
- isi konfigurasi google drive di file `.env`
- jalankan perintah `php artisan migrate`
- jalankan perintah `php artisan db:seed`
- jalankan perintah `php artisan server`
- kemudian buka browser dan akses `http://127.0.0.1:8000`
- dan login dengan username `admin` dan password `password`


### Dokumentasi
- buku manual di folder `public/BUKU MANUAL APLIKASI DISPOSISI SURAT MASUK.pdf`
- dokumentasi integrasi google drive https://github.com/pulkitjalan/google-apiclient
