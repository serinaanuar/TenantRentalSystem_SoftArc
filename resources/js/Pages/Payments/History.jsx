import React from "react";
import { Link } from "@inertiajs/react";

export default function History({ payments }) {
  return (
    <div className="container py-4">
      <h3 className="mb-3">Payment History</h3>

      {!payments || payments.length === 0 ? (
        <div className="alert alert-info">No payments yet.</div>
      ) : (
        <div className="table-responsive">
          <table className="table table-bordered align-middle">
            <thead>
              <tr>
                <th>Payment ID</th>
                <th>Invoice ID</th>
                <th>Amount Paid</th>
                <th>Method</th>
                <th>Transaction</th>
                <th>Date</th>
              </tr>
            </thead>
            <tbody>
              {payments.map((p) => (
                <tr key={p.id}>
                  <td>{p.id}</td>
                  <td>{p.invoice_id}</td>
                  <td>RM {p.amount_paid}</td>
                  <td>{p.method}</td>
                  <td>{p.transaction_id}</td>
                  <td>{p.payment_date}</td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      )}

      <div className="mt-3 d-flex gap-2">
        <Link className="btn btn-outline-primary" href="/payments/invoices">
          Back to Invoices
        </Link>
      </div>
    </div>
  );
}
