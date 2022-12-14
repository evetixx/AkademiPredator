<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Mahasiswa;
use App\Models\User;
use Hash;
use App\Charts\SampleChart;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $datas = Mahasiswa::orderByRaw('FIELD(status, "Belum Disetujui", "Disetujui")')->get();
        //print datas only dosen_wali by Auth::user()->name
        $jumlahmhs = count($datas->where('dosen_wali',Auth::user()->name));
        //print count data of status_skripsi sedang or Selesai in one variable
        $jumlahskripsi = count($datas->where('status_skripsi','Sedang')->where('dosen_wali',Auth::user()->name)) + count($datas->where('status_skripsi','Selesai')->where('dosen_wali',Auth::user()->name));
        $jumlahpkl = count($datas->where('status_pkl','Sedang')->where('dosen_wali',Auth::user()->name)) + count($datas->where('status_pkl','Selesai')->where('dosen_wali',Auth::user()->name));
        $chart = new SampleChart;
        $chart->Labels(['Sedang', 'Belum,', 'Selesai']);
        $chart->dataset('Status', 'pie', [count($datas->where('status_pkl', 'Sedang')), count($datas->where('status_pkl', 'Belum')),count($datas->where('status_pkl','Selesai')) ])->options([
            'backgroundColor' => [
                '#00FF00',
                '#FF0000',
                '#0000FF',
            ],
        ]);
        $chart_skripsi = new SampleChart;
        $chart_skripsi->Labels(['Sedang', 'Belum,', 'Selesai']);
        $chart_skripsi->dataset('Status', 'pie', [count($datas->where('status_skripsi', 'Sedang')), count($datas->where('status_skripsi', 'Belum')),count($datas->where('status_skripsi','Selesai')) ])->options([
            'backgroundColor' => [
                '#00FF00',
                '#FF0000',
                '#0000FF',
            ],
        ]);
        //make chart for angkatan mahasiswa
        $chart_mahasiswa = new SampleChart;
        $chart_mahasiswa->Labels(['20','21','22']);
        $chart_mahasiswa->dataset('Angkatan', 'pie', [count($datas->where('angkatan', '20')), count($datas->where('angkatan', '21')),count($datas->where('angkatan','22')) ])->options([
            'backgroundColor' => [
                '#00FF00',
                '#FF0000',
                '#0000FF',
            ],
        ]);
        //make variable to return teh value ofdosen_wali where user name = nama 
        $dosen_wali = Mahasiswa::select('dosen_wali')->where('nama',Auth::user()->name)->pluck('dosen_wali');
        
        $datamhs = Mahasiswa::where('nama', Auth::user()->name)->first();
        $alamatmhs = Mahasiswa::select('alamat')->where('nama',Auth::user()->name)->pluck('alamat');
        $datauser = User::where('name', Auth::user()->name)->first();
        //if role == mahasiswa view homemhs
        if(Auth::user()->role == 'mahasiswa'){

            $nipnimdoswal = User::where('name',$dosen_wali)->pluck('nipnim');
            if (Hash::check($datamhs->nim,$datauser->password)){
                Alert::error('Gagal', 'Ubah data diri dan password anda');
                return redirect()->route('profile')->with('status', 'Tolong Rubah datadiri dan password anda');}
            else{
            return view('homemhs', compact('datas', 'jumlahmhs','jumlahskripsi','jumlahpkl','chart','chart_skripsi','chart_mahasiswa','dosen_wali','datamhs','nipnimdoswal'));
            }
        }
        //if role == doswal view home
        if(Auth::user()->role == 'doswal'){
            if(Hash::check($datauser->nipnim,$datauser->password)){
                Alert::error('Gagal', 'Ubah data diri dan password anda');
                return redirect()->route('profile')->with('status', 'Tolong Rubah datadiri dan password anda');}
            else{
            return view('home', compact('datas', 'jumlahmhs','jumlahskripsi','jumlahpkl','chart','chart_skripsi','chart_mahasiswa','dosen_wali','datamhs','datauser'));
        }
    }
        if(Auth::user()->role == 'operator'){
            if(Hash::check($datauser->nipnim,$datauser->password)){
                Alert::error('Gagal', 'Ubah data diri dan password anda');
                return redirect()->route('profile')->with('status', 'Tolong Rubah datadiri dan password anda');}
            else{
            return view('homeoperator', compact('datas', 'jumlahmhs','jumlahskripsi','jumlahpkl','chart','chart_skripsi','chart_mahasiswa','dosen_wali','datamhs'));
        }
    }
        if(Auth::user()->role == 'departemen'){
            if(Hash::check($datauser->nipnim,$datauser->password)){
                Alert::error('Gagal', 'Ubah data diri dan password anda');
                return redirect()->route('profile')->with('status', 'Tolong Rubah datadiri dan password anda');}
            else{
            return view('homedpt', compact('datas', 'jumlahmhs','jumlahskripsi','jumlahpkl','chart','chart_skripsi','chart_mahasiswa','dosen_wali','datamhs'));
        }
        
    } 
}
}
