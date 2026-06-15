    </div><!-- /page-body -->
  </div><!-- /main-content -->
</div><!-- /app-layout -->
<div class="modal-overlay" id="confirmDeleteOverlay">
  <div class="modal confirm-modal">
    <div class="modal-body" style="text-align:center;padding:32px 24px;">
      <div class="confirm-icon">&#9888;&#65039;</div>
      <div class="confirm-title">Confirm Action</div>
      <p class="confirm-text" id="confirmDeleteMsg">Are you sure?</p>
    </div>
    <div class="modal-footer" style="justify-content:center;gap:12px;">
      <button class="btn btn-secondary" data-close-modal="confirmDeleteOverlay">Cancel</button>
      <button class="btn btn-danger" id="confirmDeleteBtn">Confirm</button>
    </div>
  </div>
</div>
<div class="modal-overlay" id="imageViewerOverlay">
  <div class="modal" style="max-width:600px;background:transparent;border:none;box-shadow:none;">
    <div style="text-align:right;margin-bottom:8px;"><button data-close-modal="imageViewerOverlay" style="background:rgba(0,0,0,0.6);border:none;color:#fff;width:34px;height:34px;border-radius:50%;cursor:pointer;font-size:16px;">&#x2715;</button></div>
    <img id="imageViewerImg" src="" style="width:100%;border-radius:12px;max-height:80vh;object-fit:contain;" alt="Preview">
  </div>
</div>
<script src="/gym-pro/assets/js/main.js"></script>
</body>
</html>
