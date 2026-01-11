<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

use App\Models\Invoice;
use App\Models\Payment;

use App\Payments\Adapters\FakeGatewayAdapter;
use App\Payments\Gateways\FakeGatewayApi;

class PaymentController extends Controller
{
    /**
     * List all invoices for the logged-in user
     */
    public function listInvoices()
    {
        $userId = auth()->id();

        $invoices = Invoice::where('user_id', $userId)
            ->latest()
            ->get();

        return Inertia::render('Payments/Invoices', [
            'invoices' => $invoices
        ]);
    }

    /**
     * View a single invoice for the logged-in user
     */
    public function viewInvoice($invoiceId)
    {
        $userId = auth()->id(); // tenant = user

        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return Inertia::render('Payments/Invoice', [
            'invoice' => $invoice
        ]);
    }

    /**
     * Pay rent for an invoice (Adapter Pattern used here)
     */
    public function payRent(Request $request, $invoiceId)
    {
        $request->validate([
            'method' => 'required|string',
        ]);

        $userId = auth()->id(); // tenant = user

        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // Optional safety: prevent double payment
        if (strtolower((string) $invoice->status) === 'paid' || strtoupper((string) $invoice->status) === 'PAID') {
            return redirect()->back()->with('error', 'This invoice is already paid.');
        }

        // Adapter pattern usage
        $gateway = new FakeGatewayAdapter(new FakeGatewayApi());

        $result = $gateway->charge([
            'amount'  => (float) $invoice->amount,
            'method'  => (string) $request->input('method'),
            'user_id' => (int) $userId,
        ]);

        if (!($result['success'] ?? false)) {
            return redirect()->back()->with('error', $result['message'] ?? 'Payment failed.');
        }

        // Save payment
        Payment::create([
            'invoice_id'     => $invoice->id,
            'user_id'        => $userId,
            'amount_paid'    => $invoice->amount,
            'method'         => $request->input('method'),
            'transaction_id' => $result['transaction_id'] ?? null,
            'payment_date'   => now(),
        ]);

        // Update invoice status
        $invoice->update(['status' => 'PAID']);

        return redirect()->route('payments.invoices')
            ->with('success', 'Payment successful. Transaction: ' . ($result['transaction_id'] ?? 'N/A'));
    }

    /**
     * View payment history for the logged-in user
     */
    public function viewPaymentHistory()
    {
        $userId = auth()->id();

        $payments = Payment::where('user_id', $userId)
            ->orderByDesc('payment_date')
            ->get();

        return Inertia::render('Payments/History', [
            'payments' => $payments
        ]);
    }
}
