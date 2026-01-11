import React from "react";
import { Link } from "@inertiajs/react";

export default function Invoices({ invoices }) {
  return (
    <div className="container py-4">
      <h3 className="mb-3">My Invoices</h3>

      {!invoices || invoices.length === 0 ? (
        <div className="alert alert-info">No invoices found.</div>
      ) : (
        <div className="list-group">
          {invoices.map((inv) => (
            <div key={inv.id} className="list-group-item">
              <div className="d-flex justify-content-between align-items-center">
                <div>
                  <div><b>Invoice ID:</b> {inv.id}</div>
                  <div><b>Amount:</b> RM {inv.amount}</div>
                  <div>
                    <b>Status:</b>{" "}
                    <span className={String(inv.status).toUpperCase() === "PAID" ? "text-success" : "text-danger"}>
                      {inv.status}
                    </span>
                  </div>
                </div>

                <Link className="btn btn-primary" href={`/payments/invoices/${inv.id}`}>
                  View
                </Link>
              </div>
            </div>
          ))}
        </div>
      )}

      <div className="mt-3">
        <Link className="btn btn-outline-secondary" href="/payments/history">
          View Payment History
        </Link>
      </div>
    </div>
  );
}
