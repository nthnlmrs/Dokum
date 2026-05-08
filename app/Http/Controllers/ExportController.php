<?php
namespace App\Http\Controllers;
use App\Models\Documentation;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;
use Illuminate\Support\Facades\Log;
class ExportController extends Controller
{
    public function exportPdf(Documentation \$documentation) {
        \$html = "<html><head><style>body { font-family: sans-serif; line-height: 1.6; padding: 20px; } h1 { color: #333; } pre { background: #f4f4f4; padding: 15px; border-radius: 5px; }</style></head><body><h1>{\$documentation->title}</h1><div>{\$documentation->content}</div></body></html>";
        \$pdfPath = storage_path("app/public/doc_{\$documentation->id}.pdf");
        try { Browsershot::html(\$html)->setNodeBinary('/usr/bin/node')->setNpmBinary('/usr/bin/npm')->noSandbox()->save(\$pdfPath); return response()->download(\$pdfPath)->deleteFileAfterSend(true); } catch (\Exception \$e) { Log::error("PDF Generation failed: " . \$e->getMessage()); return back()->with('error', 'Failed to generate PDF.'); }
    }
    public function exportGoogleDocs(Request \$request, Documentation \$documentation) {
        return back()->with('success', 'Successfully exported to Google Docs (Mocked)!');
    }
}
