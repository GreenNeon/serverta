<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', 'ApiController@login');
Route::get('photos/{id}', 'ResourcesController@ShowPhoto');
Route::apiResource('blog', 'BlogController')->only(['index', 'show']);
Route::group(['middleware' => 'api', 'prefix' => 'auth'], function ($router) {
	Route::post('logout', 'ApiController@logout');
	Route::post('refresh', 'ApiController@refresh');
	Route::post('me', 'ApiController@me');
});
Route::get('siswa/report', 'SiswaController@ReportSiswa');
Route::get('nilai/{kelas}/{siswa}/report', 'SiswaController@ReportNilai');
Route::get('orangtua/report', 'OrangtuaController@ReportOrangtua');

//! Kalo mau ngaktifin login lewat sini
//! Tambahin middleware api
Route::group(['middleware' => 'auth:api'], function () {
	Route::get('panel', 'ApiController@Panel');

	Route::get('siswa/search', 'SiswaController@search');
	Route::get('siswa/unattended', 'SiswaController@ShowUnattended');
	Route::apiResource('siswa', 'SiswaController');
	Route::post('siswa/{id}/link/orangtua', 'SiswaController@LinkOrangtua');
	Route::post('siswa/{id}/change/orangtua', 'SiswaController@LinkChangeOrangtua');
	Route::post('siswa/{id}/unlink/orangtua', 'SiswaController@UnlinkOrangtua');
	Route::post('siswa/{id}/link/kelas', 'SiswaController@LinkKelas');
	Route::post('siswa/{id}/unlink/kelas', 'SiswaController@UnlinkKelas');
	Route::post('siswa/{id}/link/tinggiberat', 'SiswaController@LinkTinggiBerat');
	Route::post('siswa/{id}/unlink/tinggiberat', 'SiswaController@UnlinkTinggiBerat');
	Route::get('siswa/{id}/orangtua', 'SiswaController@ShowOrangtua');
	Route::get('siswa/{id}/kelas', 'SiswaController@ShowKelas');
	Route::get('siswa/{id}/tinggiberat', 'SiswaController@ShowTinggiBerat');
	Route::post('siswa/{id}/profil', 'SiswaController@UploadProfil');
	Route::post('siswa/{id}/resetpassword', 'SiswaController@ResetPassword');
	Route::get('siswa/{id}/dashboard', 'SiswaController@ShowSiswaDashboard');
	Route::get('siswa/{id}/dashboard/nilai', 'SiswaController@ShowNilaiDashboard');
	Route::get('siswa/{id}/dashboard/catatan', 'SiswaController@ShowCatatanDashboard');


	Route::get('orangtua/search', 'OrangtuaController@search');
	Route::apiResource('orangtua', 'OrangtuaController');
	Route::post('orangtua/{id}/profil', 'OrangtuaController@UploadProfil');
	Route::get('orangtua/{id}/siswa', 'OrangtuaController@ShowSiswa');

	Route::get('pegawai/search', 'PegawaiController@search');
	Route::apiResource('pegawai', 'PegawaiController');
	Route::post('pegawai/{id}/profil', 'PegawaiController@UploadProfil');
	Route::post('pegawai/{id}/resetpassword', 'PegawaiController@ResetPassword');

	Route::get('kelas/search', 'KelasController@search');
	Route::apiResource('kelas', 'KelasController');
	Route::post('kelas/{id}/link/siswa', 'KelasController@LinkSiswa');
	Route::post('kelas/{id}/unlink/siswa', 'KelasController@UnLinkSiswa');
	Route::post('kelas/{id_kelas}/link/pembelajaran', 'KelasController@LinkPembelajaran');
	Route::post('kelas/{id_kelas}/unlink/pembelajaran', 'KelasController@UnLinkPembelajaran');
	Route::post('kelas/{id}/unlink/foto', 'KelasController@UnLinkFoto');
	Route::post('kelas/{id}/profil', 'KelasController@UploadProfil');
	Route::post('kelas/{id}/album', 'KelasController@UploadAlbum');
	Route::get('kelas/{id}/siswa', 'KelasController@ShowSiswa');
	Route::get('kelas/{id}/nilai', 'KelasController@ShowNilai');
	Route::get('kelas/{id}/pembelajaran', 'KelasController@ShowPembelajaran');
	Route::get('kelas/{id}/album', 'KelasController@ShowAlbum');

	Route::get('pembelajaran/search', 'PembelajaranController@search');
	Route::apiResource('pembelajaran', 'PembelajaranController');
	Route::get('pembelajaran/{id}/indikator', 'PembelajaranController@ShowIndikator');
	Route::post('pembelajaran/{id}/profil', 'PembelajaranController@UploadProfil');

	Route::get('indikator/search', 'IndikatorController@search');
	Route::apiResource('indikator', 'IndikatorController');
	Route::post('indikator/{id}/profil', 'IndikatorController@UploadProfil');

	Route::apiResource('nilai', 'NilaiController');

	Route::apiResource('catatan', 'CatatanAnekdotController');
	Route::post('catatan/{id}/profil', 'CatatanAnekdotController@UploadProfil');


	Route::apiResource('kegiatan', 'KegiatanController');
	Route::apiResource('blog', 'BlogController')->except(['index', 'show']);

	Route::get('tinggiberat/search', 'TinggiBeratController@search');
	Route::apiResource('tinggiberat', 'TinggiBeratController');

	Route::post('photos/profil', 'ResourcesController@UploadProfil');
});
