<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Invoice;
use App\Models\Payment;
use App\Payments\PaymentUI;

class PaymentController extends Controller
{
    private PaymentUI $paymentUI; // Target interface

    public function __construct(PaymentUI $paymentUI)
    {
        $this->paymentUI = $paymentUI;
    }

    public function viewInvoice(Request $request, $invoiceId)
    {
        $userId = auth()->id();

        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', $userId)
            ->firstOrFail();

        return view('payments.invoice', compact('invoice'));
    }

    public function payRent(Request $request, $invoiceId)
    {
        $request->validate([
            'method' => 'required|string',
        ]);

        $userId = auth()->id();

        $invoice = Invoice::where('id', $invoiceId)
            ->where('user_id', $userId)
            ->firstOrFail();

        // ✅ Adapter pattern here (Controller uses PaymentUI, not PaymentPage directly)
        $result = $this->paymentUI->handlePayment((float)$invoice->amount, (string)$request->input('method'));

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        Payment::create([
            'invoice_id' => $invoice->id,
            'user_id' => $userId,                 // ✅ user not tenant
            'amount_paid' => $invoice->amount,
            'method' => $request->input('method'),
            'transaction_id' => $result['transaction_id'],
            'payment_date' => now(),
            'status' => 'success',
        ]);

        $invoice->update(['status' => 'PAID']);

        return redirect()->route('payments.invoices')
            ->with('success', 'Payment successful. Transaction: ' . $result['transaction_id']);
    }

    public function viewPaymentHistory()
    {
        $userId = auth()->id();

        $payments = Payment::where('user_id', $userId)
            ->latest()
            ->get();

        return view('payments.history', compact('payments'));
    }
}

