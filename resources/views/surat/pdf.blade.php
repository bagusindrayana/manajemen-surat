<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Lembar Disposisi</title>
    
    <style>
        .p-3 {
            padding: 1rem !important;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }

        table.lembar-disposisi, td {
            border: 1px solid;
        }

        .lembar-disposisi td {
            padding: 5px;
        }

        .h4 {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="p-3">
        <table class="w-100 lembar-disposisi">
            <tr>
                <td colspan="3" class="text-center">
                    <h4>LEMBAR DISPOSISI</h4>
                </td>
            </tr>
            <tr>
                <td colspan="3" >
                    Nomor Surat     :   {{ $surat->nomor_surat }}
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    Tanggal Surat   :   {{ $surat->tanggal_surat }}
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    Perihal         :   {{ $surat->perihal }}
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    Sifat Surat     :   {{ $surat->sifat }}
                </td>
            </tr>
            <tr>
                <td colspan="3" class="text-center">
                    <h4>
                        DISPOSISI KEPADA
                    </h4>
                </td>
            </tr>
            
            <tr>
                <td colspan="3">
                    <ul>
                        @foreach ($surat->disposisis as $item)
                            <li>{{ $item->kepada }} : {{ $item->status }}</li>
                        @endforeach
                    </ul>
                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <u>Isi Disposisi : </u><br>
                    {!! $surat->isi !!}
                </td>
            </tr>
        </table>
        <br>
        <br>
        <p>Berkas Lampiran : </p>
        <ol>
            @foreach ($surat->berkas as $berkas)
                <li>{{ $berkas->nama_berkas }}</li>
            @endforeach
        </ol>
    </div>
</body>
</html>