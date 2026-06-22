lucide.createIcons();

let localStream = null;
const video = document.getElementById("videoElement");
const modal = document.getElementById("cameraModal");
const urlInput = document.getElementById("imageUrl");
const imgPreview = document.getElementById("form-preview");
const fallbackIcon = document.getElementById("form-fallback-icon");

function triggerFileSelect() {
  document.getElementById("local-file-ref").click();
}

function convertLocalFileBase64(input) {
  const file = input.files[0];
  if (!file) return;

  const reader = new FileReader();
  reader.onload = function (e) {
    const b64 = e.target.result;
    urlInput.value = b64;
    imgPreview.src = b64;
    imgPreview.classList.remove("hidden");
    fallbackIcon.classList.add("hidden");
  };
  reader.readAsDataURL(file);
}

function assignLibraryPath(src) {
  urlInput.value = src;
  imgPreview.src = src;
  imgPreview.classList.remove("hidden");
  fallbackIcon.classList.add("hidden");
}

async function startCamera() {
  modal.classList.remove("hidden");
  try {
    localStream = await navigator.mediaDevices.getUserMedia({
      video: {
        facingMode: "environment",
        width: { ideal: 640 },
        height: { ideal: 480 },
      },
      audio: false,
    });
    video.srcObject = localStream;
    video.play();
  } catch (err) {
    alert("Could not access default camera resources. Check browser settings.");
    stopCamera();
  }
}

function stopCamera() {
  if (localStream) {
    localStream.getTracks().forEach((track) => track.stop());
    localStream = null;
  }
  video.srcObject = null;
  modal.classList.add("hidden");
}

function takeSnapshot() {
  const canvas = document.createElement("canvas");
  canvas.width = 640;
  canvas.height = 426;
  const ctx = canvas.getContext("2d");
  ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

  const b64 = canvas.toDataURL("image/jpeg", 0.85);
  urlInput.value = b64;
  imgPreview.src = b64;
  imgPreview.classList.remove("hidden");
  fallbackIcon.classList.add("hidden");

  stopCamera();
}

// Custom Confirmation Modal Interception Handler
let activeConfirmCallback = null;

function showCustomConfirmModal(message, continueCallback) {
  document.getElementById("confirm-modal-message").textContent = message;
  activeConfirmCallback = continueCallback;
  document.getElementById("general-confirm-modal").classList.remove("hidden");
}

function dismissConfirmModal() {
  document.getElementById("general-confirm-modal").classList.add("hidden");
  activeConfirmCallback = null;
}

document
  .getElementById("confirm-modal-submit-btn")
  .addEventListener("click", () => {
    if (activeConfirmCallback) {
      activeConfirmCallback();
    }
    dismissConfirmModal();
  });

function startEditCategory(md5Hash) {
  document.getElementById("cat-view-" + md5Hash).classList.add("hidden");
  document
    .getElementById("cat-edit-form-" + md5Hash)
    .classList.remove("hidden");
  document.getElementById("cat-card-" + md5Hash).classList.remove("bg-zinc-50");
  document
    .getElementById("cat-card-" + md5Hash)
    .classList.add("bg-[#9B9B45]/5", "border-hadero-gold");
}

function cancelEditCategory(md5Hash) {
  document.getElementById("cat-view-" + md5Hash).classList.remove("hidden");
  document.getElementById("cat-edit-form-" + md5Hash).classList.add("hidden");
  document.getElementById("cat-card-" + md5Hash).classList.add("bg-zinc-50");
  document
    .getElementById("cat-card-" + md5Hash)
    .classList.remove("bg-[#9B9B45]/5", "border-hadero-gold");
}

// Real-time catalog search and category filtering logic
function filterAdminCatalog() {
  const searchText = (
    document.getElementById("admin-search-input")?.value || ""
  )
    .toLowerCase()
    .trim();
  const activeCategory = (
    document.getElementById("admin-category-filter")?.value || ""
  )
    .toLowerCase()
    .trim();

  let visibleDesktop = 0;
  let visibleMobile = 0;

  // Filter Desktop rows
  document.querySelectorAll(".admin-item-row").forEach((row) => {
    const name = row.getAttribute("data-name") || "";
    const cat = row.getAttribute("data-category") || "";
    const desc = row.getAttribute("data-description") || "";

    const matchesCategory = activeCategory === "all" || cat === activeCategory;
    const matchesSearch =
      name.includes(searchText) || desc.includes(searchText);

    if (matchesCategory && matchesSearch) {
      row.style.display = "";
      visibleDesktop++;
    } else {
      row.style.display = "none";
    }
  });

  // Filter Mobile cards
  document.querySelectorAll(".admin-item-card").forEach((card) => {
    const name = card.getAttribute("data-name") || "";
    const cat = card.getAttribute("data-category") || "";
    const desc = card.getAttribute("data-description") || "";

    const matchesCategory = activeCategory === "all" || cat === activeCategory;
    const matchesSearch =
      name.includes(searchText) || desc.includes(searchText);

    if (matchesCategory && matchesSearch) {
      card.style.display = "block";
      visibleMobile++;
    } else {
      card.style.display = "none";
    }
  });

  // Toggle visibility of empty search/filter fallback
  const adminFallback = document.getElementById("admin-no-results-msg");
  const totalVisible = Math.max(visibleDesktop, visibleMobile);
  const hasElements =
    document.querySelectorAll(".admin-item-row").length > 0 ||
    document.querySelectorAll(".admin-item-card").length > 0;

  if (adminFallback) {
    if (hasElements && totalVisible === 0) {
      adminFallback.classList.remove("hidden");
    } else {
      adminFallback.classList.add("hidden");
    }
  }
}

// Attach listeners to forms in admin.php that require verification
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll("form[data-confirm-message]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault(); // Break native submit execution
      const msg = form.getAttribute("data-confirm-message");
      showCustomConfirmModal(msg, () => {
        form.submit(); // Perform form submit programmatically
      });
    });
  });
});
