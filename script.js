"use strict";

// Mobile navigation
const menuButton = document.querySelector(".menu-toggle");
const navigation = document.querySelector("#main-navigation");

if (menuButton && navigation) {
    const mobileScreen = window.matchMedia("(max-width: 760px)");

    function setMenuOpen(open) {
        navigation.classList.toggle("is-collapsed", !open);
        menuButton.setAttribute("aria-expanded", String(open));
    }

    function updateNavigation() {
        menuButton.hidden = !mobileScreen.matches;
        setMenuOpen(!mobileScreen.matches);
    }

    updateNavigation();
    mobileScreen.addEventListener("change", updateNavigation);

    menuButton.addEventListener("click", () => {
        const isOpen = menuButton.getAttribute("aria-expanded") === "true";
        setMenuOpen(!isOpen);
    });

    navigation.querySelectorAll("a").forEach((link) => {
        link.addEventListener("click", () => {
            if (mobileScreen.matches) {
                setMenuOpen(false);
            }
        });
    });

    document.addEventListener("keydown", (event) => {
        if (
            event.key === "Escape" &&
            mobileScreen.matches &&
            menuButton.getAttribute("aria-expanded") === "true"
        ) {
            setMenuOpen(false);
            menuButton.focus();
        }
    });
}

// Prototype calculator: replace sample rates with database rates later.
const estimateForm = document.querySelector("#estimate-form");

if (estimateForm) {
    const result = document.querySelector("#estimate-result");

    const sampleRates = {
        black_white: 1.00,
        colour: 2.00
    };

    const currency = new Intl.NumberFormat("en-GH", {
        style: "currency",
        currency: "GHS"
    });

    estimateForm.addEventListener("submit", (event) => {
        event.preventDefault();

        const pages = Number(document.querySelector("#page-count").value);
        const copies = Number(document.querySelector("#copies").value);
        const colourMode = document.querySelector("#colour-mode,#color_mode").value;
        const rate = sampleRates[colourMode];

        if (
            !Number.isSafeInteger(pages) ||
            !Number.isSafeInteger(copies) ||
            pages < 1 ||
            copies < 1 ||
            typeof rate !== "number"
        ) {
            result.textContent =
                "Enter valid positive whole numbers for pages and copies.";
            return;
        }

        const printedPages = pages * copies;
        const totalCents = printedPages * Math.round(rate * 100);

        if (
            !Number.isSafeInteger(printedPages) ||
            !Number.isSafeInteger(totalCents)
        ) {
            result.textContent =
                "The quantity is too large. Enter smaller values.";
            return;
        }

        result.textContent =
            `Sample estimate: ${currency.format(totalCents / 100)} ` +
            `for ${printedPages} printed page sides. ` +
            "Final price must be confirmed by staff.";
    });
}
