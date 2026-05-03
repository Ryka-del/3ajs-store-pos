function filterProducts(categoryId) {
    fetch("ajax/filter_products.php?category_id=" + categoryId)
        .then(response => response.text())
        .then(data => {
            document.getElementById("product-grid").innerHTML = data;
        })
        .catch(err => console.error(err));
}

//Scroll for categories
function scrollCategories(amount) {
    const container = document.getElementById("categoriesRow");
    container.scrollBy({
        left: amount,
        behavior: "smooth"
    });
}


// Enhanced Mobile Navigation Toggle
document.addEventListener('DOMContentLoaded', function () {
    const menuToggle = document.getElementById("menuToggle");
    const navMenu = document.getElementById("navMenu");
    const body = document.body;

    if (menuToggle && navMenu) {
        // Toggle menu visibility
        menuToggle.addEventListener("click", (e) => {
            e.preventDefault();
            e.stopPropagation();

            // Toggle menu visibility
            navMenu.classList.toggle("hidden");

            // Toggle hamburger icon
            const icon = menuToggle.querySelector('i');
            if (navMenu.classList.contains("hidden")) {
                icon.className = "bx bx-menu";
                body.style.overflow = "auto"; // Restore scrolling
            } else {
                icon.className = "bx bx-x";
                body.style.overflow = "hidden"; // Prevent background scrolling
            }
        });

        // Close menu when clicking outside
        document.addEventListener("click", (e) => {
            if (!navMenu.contains(e.target) && !menuToggle.contains(e.target)) {
                if (!navMenu.classList.contains("hidden")) {
                    navMenu.classList.add("hidden");
                    const icon = menuToggle.querySelector('i');
                    icon.className = "bx bx-menu";
                    body.style.overflow = "auto";
                }
            }
        });

        // Close menu when clicking on a navigation link
        const navLinks = navMenu.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                navMenu.classList.add("hidden");
                const icon = menuToggle.querySelector('i');
                icon.className = "bx bx-menu";
                body.style.overflow = "auto";
            });
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 1024) { // lg breakpoint
                navMenu.classList.add("hidden");
                const icon = menuToggle.querySelector('i');
                icon.className = "bx bx-menu";
                body.style.overflow = "auto";
            }
        });

        // Add smooth slide animation
        navMenu.style.transition = "all 0.3s ease-in-out";
    }
});

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".reveal").forEach((el, index) => {
        el.style.transitionDelay = `${Math.min(index * 45, 360)}ms`;
    });

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                entry.target.classList.add("reveal-in");
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.12 });

    document.querySelectorAll(".reveal").forEach((el) => observer.observe(el));
});

document.addEventListener("DOMContentLoaded", () => {
    if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

    document.querySelectorAll(".tilt-card").forEach((card) => {
        card.addEventListener("mousemove", (event) => {
            const rect = card.getBoundingClientRect();
            const px = (event.clientX - rect.left) / rect.width;
            const py = (event.clientY - rect.top) / rect.height;
            const rotateY = (px - 0.5) * 12;
            const rotateX = (0.5 - py) * 12;
            card.style.transform = `perspective(1200px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) translateY(-6px)`;
        });

        card.addEventListener("mouseleave", () => {
            card.style.transform = "";
        });
    });
});


// Highlight current page in navigation
const currentPage = window.location.pathname.split("/").pop();
document.querySelectorAll("nav a").forEach(link => {
    if (link.getAttribute("href") === currentPage) {
        link.classList.remove("text-gray-700", "hover:bg-gray-200");
        link.classList.add("bg-blue-500", "text-white", "is-active");
    }
});



//let stream = null;

// Open modal
function openAddProductModal() {
    document.getElementById("addProductModal").classList.remove("hidden");
}

// Close modal
function closeAddProductModal() {
    document.getElementById("addProductModal").classList.add("hidden");
    stopScanner(); // ✅ stop camera if modal closes
}

// Barcode scanner using ZXing library

let codeReader;

async function startScanner() {
    const preview = document.getElementById("scannerPreview");
    const container = document.getElementById("scannerContainer");
    container.classList.remove("hidden");

    if (!codeReader) {
        codeReader = new ZXing.BrowserMultiFormatReader();
    }

    try {
        // Force scanner to try multiple formats (not only QR)
        const formats = [
            ZXing.BarcodeFormat.EAN_13,
            ZXing.BarcodeFormat.EAN_8,
            ZXing.BarcodeFormat.UPC_A,
            ZXing.BarcodeFormat.UPC_E,
            ZXing.BarcodeFormat.CODE_128,
            ZXing.BarcodeFormat.CODE_39
        ];

        const hints = new Map();
        hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, formats);

        await codeReader.decodeFromVideoDevice(null, preview, (result, err) => {
            if (result) {
                console.log("Detected:", result.text, result.getBarcodeFormat());
                document.getElementById("barcodeInput").value = result.text;
                stopScanner(); // stop after success
            }
        }, hints);
    } catch (err) {
        console.error("Error starting scanner:", err);
        Swal.fire({
            icon: "error",
            title: "Scanner Error",
            text: `Error starting scanner: ${err.message}`,
            confirmButtonColor: "#d33"
        });
    }
}

function stopScanner() {
    if (codeReader) {
        codeReader.reset();
    }
    document.getElementById("scannerContainer").classList.add("hidden");
}


document.addEventListener("DOMContentLoaded", () => {
    const barcodeInput = document.getElementById("barcodeInput");

    // When barcode is scanned and input changes
    if (barcodeInput) {
        barcodeInput.addEventListener("change", () => {
            const barcode = barcodeInput.value.trim();

            if (barcode !== "") {
                fetch(`get_product.php?barcode=${barcode}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            // Auto-fill form
                            document.querySelector("input[name='name']").value = data.name;
                            document.querySelector("select[name='category']").value = data.category;
                        } else {
                            Swal.fire({
                                icon: "warning",
                                title: "⚠️ Product Not Found",
                                text: "Please enter details manually.",
                                confirmButtonColor: "#f59e0b" // amber tone
                            });
                        }
                    })
                    .catch(err => console.error("Error fetching product:", err));
            }
        });
    }
});

// Reusable function to fetch product info and autofill fields
async function fetchAndFillProduct(barcode) {
    if (!barcode) return;

    try {
        const response = await fetch(`https://world.openfoodfacts.org/api/v0/product/${barcode}.json`);
        const data = await response.json();

        if (data.status === 1) {
            const product = data.product;

            // Autofill Product Name
            if (product.product_name) {
                document.getElementById("productName").value = product.product_name;
            }

            // Autofill Category
            if (product.categories_tags && product.categories_tags.length > 0) {
                const category = product.categories_tags[0].replace("en:", "");
                const categorySelect = document.getElementById("categorySelect");

                // Match against dropdown values
                const option = [...categorySelect.options].find(opt =>
                    opt.value.toLowerCase() === category.toLowerCase()
                );

                if (option) {
                    categorySelect.value = option.value;
                }
            }

            console.log("✅ Product found:", product.product_name);
        } else {
            Swal.fire({
                icon: "warning",
                title: "⚠️ Product Not Found",
                text: "Not found in Open Food Facts. Enter details manually.",
                confirmButtonColor: "#f59e0b"
            });
        }
    } catch (err) {
        console.error("Error fetching product info:", err);
    }
}

// When a barcode is scanned from camera/scanner
function onBarcodeScanned(barcode) {
    console.log("Scanned barcode:", barcode);

    document.getElementById("barcodeInput").value = barcode;
    fetchAndFillProduct(barcode);
}

// When user types or pastes a barcode manually
(function () {
    const barcodeInputEl = document.getElementById("barcodeInput");
    if (barcodeInputEl) {
        barcodeInputEl.addEventListener("change", function () {
            fetchAndFillProduct(this.value.trim());
        });
    }
})();


// Image preview for upload products
document.addEventListener("DOMContentLoaded", () => {
    const imageUpload = document.getElementById("imageUpload");
    const imagePreview = document.getElementById("imagePreview");
    const uploadText = document.getElementById("uploadText");

    if (imageUpload) {
        imageUpload.addEventListener("change", (event) => {
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    imagePreview.src = e.target.result;      // Show image
                    imagePreview.classList.remove("hidden"); // Unhide image
                    uploadText.classList.add("hidden");      // Hide text
                };
                reader.readAsDataURL(file);
            }
        });
    }
});

(function () {
    const addProductForm = document.getElementById("addProductForm");
    if (!addProductForm) return;

    addProductForm.addEventListener("submit", async function (e) {
        e.preventDefault(); // stop normal page reload

        let formData = new FormData(this);

        let response = await fetch("products.php", {
            method: "POST",
            body: formData
        });

        let result = await response.json(); // expect JSON response
        if (result.success) {
            Swal.fire("✅ Success", "Product added successfully!", "success");
            closeAddProductModal();
            location.reload(); // reload products grid
        } else {
            Swal.fire({
                icon: "error",
                title: "❌ Error",
                text: result.message,
                confirmButtonColor: "#d33"
            });
        }
    });
})();
// Shopping cart functionality

// Load cart from localStorage or initialize empty cart

let cart = JSON.parse(localStorage.getItem("cart")) || {};

// Add product to cart
function addToCart(id, name, price, image) {
    price = parseFloat(price);

    if (cart[id]) {
        cart[id].qty++;
    } else {
        cart[id] = { name, price, qty: 1, image };
    }

    saveCart();
    updateCartUI();
}

// Increase quantity
function increaseQty(id) {
    cart[id].qty++;
    saveCart();
    updateCartUI();
}

// Decrease quantity
function decreaseQty(id) {
    if (cart[id].qty > 1) {
        cart[id].qty--;
    } else {
        delete cart[id];
    }
    saveCart();
    updateCartUI();
}

function clearCart() {
    if (!cart || Object.keys(cart).length === 0) {
        Swal.fire("Empty Cart", "Your cart is already empty.", "info");
        return;
    }
    Swal.fire({
        title: 'Clear Cart?',
        text: "Are you sure you want to clear the cart?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, clear it!'
    }).then((result) => {
        if (result.isConfirmed) {
            cart = {};
            saveCart();
            updateCartUI();
            Swal.fire("Cleared", "Cart has been cleared.", "success");
        }
    });
}

// Save cart to localStorage
function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}

// Helper to compute current total
function computeCartTotal() {
    let total = 0;
    for (let id in cart) {
        const item = cart[id];
        total += item.price * item.qty;
    }
    return total;
}

// Render cart items in sidebar
function updateCartUI() {
    const cartContainer = document.querySelector("#cart-items"); // target the sidebar list
    if (!cartContainer) return; // stop if element not found

    cartContainer.innerHTML = "";

    let total = 0;
    const cartIds = Object.keys(cart);

    if (cartIds.length === 0) {
        // Render empty state
        const empty = document.createElement("div");
        empty.className = "flex flex-col items-center justify-center text-gray-400 py-8 mt-52";
        empty.innerHTML = `
            <i class="fa-solid fa-cart-shopping text-4xl mb-2 text-[#3498DB]"></i>
            <p class="text-sm">No items yet...</p>
        `;
        cartContainer.appendChild(empty);
    } else {
        for (let id of cartIds) {
            let item = cart[id];
            total += item.price * item.qty;

            const div = document.createElement("div");
            div.className = "flex justify-between items-center bg-gray-100 w-full p-2 rounded-lg";

            div.innerHTML = `
                <div class="flex items-center space-x-2">
                    <img src="${item.image}" alt="${item.name}" class="w-12 h-12 rounded-lg object-cover">
                    <div>
                        <p class="md:text-[10px] xl:text-base font-light">${item.name}</p>
                        <p class="md:text-[8px] xl:text-xs font-semibold">₱${item.price.toFixed(2)}</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button onclick="decreaseQty('${id}')"
                        class="bg-gray-300 text-red-400 md:w-5 md:h-5 xl:w-8 xl:h-8 rounded-lg hover:bg-gray-400">
                        ${item.qty > 1 ? `<i class="bx bx-minus md:text-xs font-bold"></i>` : `<i class="bx bx-trash"></i>`}
                    </button>
                    <span class="font-semibold md:text-xs">${item.qty}</span>
                    <button onclick="increaseQty('${id}')"
                        class="bg-gray-300 text-gray-700 xl:px-2 xl:py-1 md:w-5 md:h-5 xl:w-8 xl:h-8 rounded-lg hover:bg-gray-400"><i class='bx bx-plus md:text-xs'></i></button>
                </div>
            `;
            cartContainer.appendChild(div);
        }
    }

    const totalPriceEl = document.getElementById("total-price");
    if (totalPriceEl) totalPriceEl.textContent = "₱" + total.toFixed(2);

    const modalTotalEl = document.getElementById("modal-total");
    if (modalTotalEl) modalTotalEl.textContent = "₱" + total.toFixed(2);

    // Update change live if modal is open
    const paymentInput = document.getElementById("paymentInput");
    const changeEl = document.getElementById("modal-change");
    if (paymentInput && changeEl) {
        const payment = parseFloat(paymentInput.value) || 0;
        const change = Math.max(0, payment - total);
        changeEl.textContent = "₱" + change.toFixed(2);
    }
}

// Checkout modal controls
function openCheckoutModal() {
    document.getElementById("checkoutModal").classList.remove("hidden");

    // Initialize totals
    const total = computeCartTotal();
    const modalTotalEl = document.getElementById("modal-total");
    if (modalTotalEl) modalTotalEl.textContent = "₱" + total.toFixed(2);

    // Reset payment + change display
    const paymentInput = document.getElementById("paymentInput");
    const changeEl = document.getElementById("modal-change");
    if (paymentInput) paymentInput.value = "";
    if (changeEl) changeEl.textContent = "₱0.00";
}

function closeCheckoutModal() {
    document.getElementById("checkoutModal").classList.add("hidden");
}

// Handle payment & send to server
function payNow() {
    const paymentType = (document.getElementById("paymentType")?.value) || "pay";
    const paymentInput = document.getElementById("paymentInput");
    const paymentAmount = parseFloat(paymentInput?.value) || 0;

    let total = computeCartTotal();

    if (total <= 0 || !cart || Object.keys(cart).length === 0) {
        Swal.fire({
            icon: "info",
            title: "Empty Cart",
            text: "Please add an item before checkout.",
            confirmButtonColor: "#3085d6"
        });
        return;
    }

    // Validation only if paymentType = "pay"
    if (paymentType === "pay") {
        if (isNaN(paymentAmount) || paymentAmount < total) {
            Swal.fire({
                icon: 'error',
                title: 'Insufficient Payment',
                text: `You still need ₱${(total - paymentAmount).toFixed(2)} to complete this purchase.`,
                confirmButtonColor: '#3085d6',
                confirmButtonText: 'OK'
            });
            return;
        }
    }

    // For debt, always send payment = 0
    const finalPayment = (paymentType === "debt") ? 0 : paymentAmount;

    // Send cart to checkout.php
    fetch("checkout.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
            cart: cart,
            total: total,
            payment: finalPayment,
            paymentType: paymentType
        })
    })
        .then(async res => {
            const text = await res.text();
            try {
                return JSON.parse(text);
            } catch (error) {
                throw new Error(text || "Checkout returned an invalid response.");
            }
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: paymentType === "pay" ? 'Transaction Successful' : 'Debt Recorded',
                    text: `Transaction ID: ${data.order_id}`,
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'OK'
                });
                const txEl = document.getElementById("last-transaction-id");
                if (txEl) txEl.textContent = data.order_id;

                // Reset cart
                cart = {};
                saveCart();
                updateCartUI();
                closeCheckoutModal();
            } else {
                Swal.fire({
                    icon: "error",
                    title: "❌ Error",
                    text: data.message,
                    confirmButtonColor: "#d33"
                });
            }
        })
        .catch(err => {
            console.error("Checkout error:", err);
            Swal.fire({
                icon: "error",
                title: "Checkout Failed",
                text: err.message || "Something went wrong while processing payment.",
                confirmButtonColor: "#d33"
            });
        });
}

// Load cart when page loads
document.addEventListener("DOMContentLoaded", updateCartUI);

// Live update change on typing amount
document.addEventListener("DOMContentLoaded", function () {
    const paymentInput = document.getElementById("paymentInput");
    if (!paymentInput) return;

    paymentInput.addEventListener("input", function () {
        const total = computeCartTotal();
        const amount = parseFloat(this.value) || 0;
        const change = Math.max(0, amount - total);
        const changeEl = document.getElementById("modal-change");
        if (changeEl) changeEl.textContent = "₱" + change.toFixed(2);
    });
});


// Page transition animations
window.addEventListener("DOMContentLoaded", () => {
    const page = document.getElementById("page-transition");
    if (!page) return;

    page.classList.remove("translate-y-5", "opacity-0");
    page.classList.add("translate-y-0", "opacity-100");

    document.querySelectorAll("a").forEach(link => {
        if (link.hostname === window.location.hostname) {
            link.addEventListener("click", e => {
                const href = link.getAttribute("href");
                if (!href || href.startsWith("#") || href === "") return;

                e.preventDefault();
                page.classList.remove("translate-y-0", "opacity-100");
                page.classList.add("translate-y-5", "opacity-0");
                setTimeout(() => { window.location = href; }, 500);
            });
        }
    });
});

window.addEventListener("pageshow", () => {
    const page = document.getElementById("page-transition");
    if (page) {
        page.classList.remove("translate-y-5", "opacity-0");
        page.classList.add("translate-y-0", "opacity-100");
    }
});

window.addEventListener("load", () => {
    const loader = document.getElementById("page-loader");
    if (!loader) return;
    loader.style.display = "none"; // hide loader once everything is loaded
});




let homeScanActive = false;
let lastScanCode = null;
let lastScanAt = 0; // ms timestamp

// Expose scan controls globally for inline onclick handlers
window.openScanModal = function () {
    const modal = document.getElementById("scanModal");
    if (!modal) return;
    modal.classList.remove("hidden");
    startCartScanner();
};

window.closeScanModal = function () {
    stopCartScanner();
    const modal = document.getElementById("scanModal");
    if (modal) modal.classList.add("hidden");
};

// Start scanner for Home page → continuously adds products to cart
window.startCartScanner = async function () {
    const preview = document.getElementById("homeScannerPreview");
    const container = document.getElementById("homeScannerContainer");
    if (!preview || !container) return;

    container.classList.remove("hidden");

    if (!window.ZXing) {
        Swal.fire({
            icon: "error",
            title: "Scanner Library Missing",
            text: "The scanner library could not be loaded.",
            confirmButtonColor: "#d33"
        });
        return;
    }

    if (!window.codeReader) {
        window.codeReader = new ZXing.BrowserMultiFormatReader();
    }

    try {
        const formats = [
            ZXing.BarcodeFormat.EAN_13,
            ZXing.BarcodeFormat.EAN_8,
            ZXing.BarcodeFormat.UPC_A,
            ZXing.BarcodeFormat.UPC_E,
            ZXing.BarcodeFormat.CODE_128,
            ZXing.BarcodeFormat.CODE_39
        ];

        const hints = new Map();
        hints.set(ZXing.DecodeHintType.POSSIBLE_FORMATS, formats);

        homeScanActive = true;

        await window.codeReader.decodeFromVideoDevice(null, preview, async (result, err) => {
            if (!homeScanActive || !result) return;

            const code = result.text;
            const now = Date.now();

            // Debounce: ignore same code within 1.5s
            if (code === lastScanCode && (now - lastScanAt) < 1500) return;

            lastScanCode = code;
            lastScanAt = now;

            try {
                const res = await fetch(`ajax/get_product.php?barcode=${encodeURIComponent(code)}`);
                const data = await res.json();
                if (data.success && data.product) {
                    const p = data.product;

                    // ✅ Play success beep
                    const successBeep = document.getElementById("successBeep");
                    if (successBeep) {
                        successBeep.currentTime = 0;
                        successBeep.play().catch(err => console.warn("Beep failed:", err));
                    }

                    addToCart(String(p.id), p.name, String(p.price), p.image_url || "");
                    Swal.fire({
                        icon: "success",
                        title: "Added",
                        text: `${p.name} added to cart.`,
                        timer: 900,
                        showConfirmButton: false
                    });
                    // Keep scanning; do not close modal
                } else {
                    // ❌ Play error beep
                    const errorBeep = document.getElementById("errorBeep");
                    if (errorBeep) {
                        errorBeep.currentTime = 0;
                        errorBeep.play().catch(err => console.warn("Error beep failed:", err));
                    }

                    Swal.fire({
                        icon: "warning",
                        title: "Not found",
                        text: "No product matched that barcode.",
                        timer: 1200,
                        showConfirmButton: false
                    });
                }
            } catch (e) {
                console.error(e);

                // ❌ Play error beep on failure
                const errorBeep = document.getElementById("errorBeep");
                if (errorBeep) {
                    errorBeep.currentTime = 0;
                    errorBeep.play().catch(err => console.warn("Error beep failed:", err));
                }

                Swal.fire({
                    icon: "error",
                    title: "Lookup failed",
                    text: "Failed to look up product.",
                    timer: 1200,
                    showConfirmButton: false
                });
            }
        }, hints);
    } catch (err) {
        console.error("Error starting scanner:", err);
        Swal.fire({
            icon: "error",
            title: "Scanner Error",
            text: `Error starting scanner: ${err.message}`,
            confirmButtonColor: "#d33"
        });
    }
};

window.stopCartScanner = function () {
    homeScanActive = false;
    if (window.codeReader) {
        window.codeReader.reset();
    }
    const container = document.getElementById("homeScannerContainer");
    if (container) container.classList.add("hidden");
    lastScanCode = null;
    lastScanAt = 0;
};

function toggleButtons(card) {
    // find the parent (card wrapper)
    const wrapper = card.parentElement;
    const buttons = wrapper.querySelector(".action-buttons");
    buttons.classList.toggle("hidden");
}




// Reusable Product Modal (Add/Edit)
function openProductModal(mode, triggerEl = null) {
    const modal = document.getElementById("addProductModal");
    const title = document.getElementById("productModalTitle");
    const submitBtn = document.getElementById("productSubmitBtn");
    const form = document.getElementById("productForm");

    resetProductForm();

    if (mode === "edit" && triggerEl) {
        title.textContent = "Edit Product";
        submitBtn.textContent = "Update Product";

        // Fill values from data attributes
        document.getElementById("productId").value = triggerEl.dataset.id || "";
        document.getElementById("barcodeInput").value = triggerEl.dataset.barcode || "";
        document.getElementById("productName").value = triggerEl.dataset.name || "";
        document.getElementById("categorySelect").value = triggerEl.dataset.category || "";
        document.getElementById("costPrice").value = triggerEl.dataset.cost || "";
        document.getElementById("sellPrice").value = triggerEl.dataset.price || "";
        document.getElementById("qty").value = triggerEl.dataset.qty || "";

        // Preview existing image if any
        const img = document.getElementById("imagePreview");
        const uploadText = document.getElementById("uploadText");
        if (triggerEl.dataset.image) {
            img.src = triggerEl.dataset.image;
            img.classList.remove("hidden");
            uploadText.classList.add("hidden");
        }
    } else {
        title.textContent = "Add Product";
        submitBtn.textContent = "Save Product";
    }

    modal.classList.remove("hidden");
}

function closeProductModal() {
    document.getElementById("addProductModal").classList.add("hidden");
    stopScanner();
}

function resetProductForm() {
    const form = document.getElementById("productForm");
    form.reset();
    document.getElementById("productId").value = "";

    const img = document.getElementById("imagePreview");
    const uploadText = document.getElementById("uploadText");
    if (img) {
        img.src = "";
        img.classList.add("hidden");
    }
    if (uploadText) uploadText.classList.remove("hidden");
}

// Toggle buttons under product card (uses your existing function name)
window.toggleButtons = function (card) {
    const wrapper = card.parentElement;
    const buttons = wrapper.querySelector(".action-buttons");
    buttons.classList.toggle("hidden");
};


// charts.js

document.addEventListener("DOMContentLoaded", () => {
    // Sales Chart (Line Chart)
    const salesCanvas = document.getElementById("salesChart");
    const topCanvas = document.getElementById("topChart");
    if (!window.Chart || !salesCanvas || !topCanvas) return;

    const salesCtx = salesCanvas.getContext("2d");
    new Chart(salesCtx, {
        type: "line",
        data: {
            labels: salesLabels,
            datasets: [{
                label: "Daily Sales",
                data: salesValues,
                borderColor: "#7c3aed", // purple
                backgroundColor: "rgba(124, 58, 237, 0.2)",
                fill: true,
                tension: 0.3,
                pointBackgroundColor: "#7c3aed",
                pointRadius: 5
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true },
                tooltip: { mode: "index", intersect: false }
            },
            scales: {
                x: {
                    title: { display: true, text: "Date" }
                },
                y: {
                    title: { display: true, text: "Sales (₱)" },
                    beginAtZero: true
                }
            }
        }
    });

    // Top Products Chart (Bar Chart)
    const topCtx = topCanvas.getContext("2d");
    new Chart(topCtx, {
        type: "bar",
        data: {
            labels: topLabels,
            datasets: [{
                label: "Units Sold",
                data: topValues,
                backgroundColor: [
                    "#7c3aed", "#3b82f6", "#f59e0b", "#ef4444", "#10b981"
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { mode: "index", intersect: false }
            },
            scales: {
                x: {
                    title: { display: true, text: "Product" }
                },
                y: {
                    title: { display: true, text: "Quantity" },
                    beginAtZero: true
                }
            }
        }
    });
});


document.addEventListener("DOMContentLoaded", () => {
    const rangeSelect = document.getElementById("rangeSelect");
    const customWrapper = document.getElementById("customDateWrapper");
    if (!rangeSelect || !customWrapper) return;

    function toggleCustomDates() {
        if (rangeSelect.value === "custom") {
            customWrapper.classList.remove("hidden");
        } else {
            customWrapper.classList.add("hidden");
        }
    }

    // Run once on load
    toggleCustomDates();

    // Run when dropdown changes
    rangeSelect.addEventListener("change", toggleCustomDates);
});

document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("txFilterForm");
    if (!form) return;
    form.querySelectorAll("input[type=date]").forEach(input => {
        input.addEventListener("change", () => {
            form.submit();
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const rangeSelect = document.getElementById('rangeSelect');
    const customWrapper = document.getElementById('customDateWrapper');
    const startInput = document.getElementById('startDate');
    const endInput = document.getElementById('endDate');
    if (!rangeSelect || !customWrapper || !startInput || !endInput) return;

    // Helper to get URL param
    const getParam = (k) => new URLSearchParams(window.location.search).get(k);

    // Navigate to url with updated params
    function navigateWithParams(params) {
        const url = new URL(window.location.href);
        // set or delete params based on keys in params object
        Object.keys(params).forEach(key => {
            const val = params[key];
            if (val === null || val === undefined) url.searchParams.delete(key);
            else url.searchParams.set(key, val);
        });
        window.location.href = url.toString();
    }

    // When user chooses a range
    rangeSelect.addEventListener('change', () => {
        const val = rangeSelect.value;
        if (val === 'custom') {
            // show custom inputs and focus start
            customWrapper.classList.remove('hidden');
            startInput.focus();
            return;
        }
        // hide custom quickly and navigate with the selected preset
        customWrapper.classList.add('hidden');
        navigateWithParams({ range: val, start: null, end: null });
    });

    // Auto-submit when both custom dates are selected
    function trySubmitCustom() {
        if (rangeSelect.value !== 'custom') return;
        const s = startInput.value;
        const e = endInput.value;
        if (!s || !e) return;
        if (s > e) {
            alert('Start date cannot be after End date.');
            return;
        }
        navigateWithParams({ range: 'custom', start: s, end: e });
    }

    startInput.addEventListener('change', trySubmitCustom);
    endInput.addEventListener('change', trySubmitCustom);

    // Initialize UI on page load: show custom wrapper only if URL or select says custom
    const currentRange = getParam('range') || rangeSelect.value;
    if (currentRange === 'custom' || rangeSelect.value === 'custom') {
        customWrapper.classList.remove('hidden');
    } else {
        customWrapper.classList.add('hidden');
    }
});



function openEditModal(orderId, paidAmount = '', paymentType = 'pay') {
    document.getElementById('editModal').classList.remove('hidden');
    const modalContent = document.getElementById('editModalContent');
    modalContent.classList.remove('scale-95', 'opacity-0');
    modalContent.classList.add('scale-100', 'opacity-100');

    document.getElementById('editOrderId').value = orderId;
    document.getElementById('editAmount').value = paidAmount;
    document.getElementById('editPaymentType').value = paymentType;
}

function closeEditModal() {
    const modal = document.getElementById('editModal');
    const modalContent = document.getElementById('editModalContent');

    modalContent.classList.remove('scale-100', 'opacity-100');
    modalContent.classList.add('scale-95', 'opacity-0');

    setTimeout(() => modal.classList.add('hidden'), 200); // delay for smooth fade-out
}
