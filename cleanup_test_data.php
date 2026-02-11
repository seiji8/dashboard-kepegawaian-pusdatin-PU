<?php
use App\Models\Pegawai;
use Illuminate\Support\Facades\Schema;

// Delete test data
Pegawai::destroy([9991, 9992, 9993]);

echo "Test data deleted.\n";
