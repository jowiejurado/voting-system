// resources/js/qr-scan.js
import { Html5QrcodeScanner } from "html5-qrcode";

const statusEl = document.getElementById("status");
const setStatus = (t) => (statusEl.textContent = t ?? "");

const onScanSuccess = (decodedText /* string */) => {
  setStatus("QR detected. Verifying…");
  // If your QR encodes the signed URL, just follow it:
  if (decodedText.startsWith("http")) {
    window.location.href = decodedText; // hits /qr/verify?code=...&signature=...
    return;
  }
  // Otherwise, post the decodedText to your API for verification
  fetch("/api/qr/verify", {
    method: "POST",
    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content },
    body: JSON.stringify({ payload: decodedText }),
  }).then(res => res.json()).then(data => {
    setStatus(data.message || "Verified.");
  }).catch(() => setStatus("Verification failed."));
};

const onScanFailure = /* optional */ () => {};

new Html5QrcodeScanner("reader", { fps: 10, qrbox: 250 })
  .render(onScanSuccess, onScanFailure);
