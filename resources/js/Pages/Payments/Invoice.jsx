import React, { useState } from "react";
import { Link, router, usePage } from "@inertiajs/react";

export default function Invoice({ invoice }) {
  const { flash } = usePage().props;
  const [method, setMethod] = useState("card");
  const isPaid = String(invoice.status).toUpperCase() === "PAID";

  const payNow = () => {
    router.post(`/payments/invoices/${invoice.id}/pay`, { method });
  };

  return (
    <div className="container py-4">
      <h3 className="mb-3">Invoice Details</h3>

      {flash?.success && <div className="alert alert-success">{flash.success}</div>}
      {flash?.error && <div className="alert alert-danger">{flash.error}</div>}

      <div className="card">
        <div className="card-body">
          <p><b>Invoice ID:</b> {invoice.id}</p>
          <p><b>Amount:</b> RM {invoice.amount}</p>
          <p>
            <b>Status:</b>{" "}
            <span className={isPaid ? "text-success" : "text-danger"}>
              {invoice.status}
            </span>
          </p>

          <hr />

          <h5>Pay Rent</h5>

          {isPaid ? (
            <div className="alert alert-success mb-0">This invoice is already PAID.</div>
          ) : (
            <>
              <div className="mb-3">
                <label className="form-label">Payment Method</label>
                <select
                  className="form-select"
                  value={method}
                  onChange={(e) => setMethod(e.target.value)}
                >
                  <option value="card">Card</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="cash">Cash</option>
                </select>
              </div>

              <button className="btn btn-success" onClick={payNow}>
                Pay Now
              </button>
            </>
          )}
        </div>
      </div>

      <div className="mt-3 d-flex gap-2">
        <Link className="btn btn-outline-primary" href="/payments/invoices">
          Back to Invoices
        </Link>
        <Link className="btn btn-outline-secondary" href="/payments/history">
          Payment History
        </Link>
      </div>
    </div>
  );
}
