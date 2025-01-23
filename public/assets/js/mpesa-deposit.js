document.getElementById('mpesaDepositForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;

    const formData = new FormData(form);
    const loader = document.getElementById("loader");
    loader.style.display = "block";
    try {
        const response = await fetch("/wallets/deposit", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: formData,
        });

        loader.style.display = "none";

        const result = await response.json();

        if (result.success) {
            notyf.success("Deposit initiated successfully");
            const modalElement = document.getElementById('depositModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            notyf.error(result.message || 'An error occurred.');
        }
    } catch (err) {
        loader.style.display = "none";

        notyf.error('An error occurred. Please try again.');
    }
});

document.getElementById('mpesaWithdrawalForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;

    const formData = new FormData(form);
    const loader = document.getElementById("loader");
    loader.style.display = "block";
    try {
        const response = await fetch("/wallets/withdraw", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: formData,
        });

        loader.style.display = "none";

        const result = await response.json();

        if (result.success) {
            notyf.success("Withdrawal initiated successfully");
            const modalElement = document.getElementById('withdrawModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            notyf.error(result.message || 'An error occurred.');
        }
    } catch (err) {
        loader.style.display = "none";

        notyf.error('An error occurred. Please try again.');
    }
});

document.getElementById('fundTransferForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;

    const formData = new FormData(form);
    const loader = document.getElementById("loader");
    loader.style.display = "block";
    try {
        const response = await fetch("/wallets/transfer", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: formData,
        });

        loader.style.display = "none";

        const result = await response.json();

        if (result.success) {
            notyf.success(result.message);
            const modalElement = document.getElementById('transferModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            notyf.error(result.message || 'An error occurred.');
        }
    } catch (err) {
        loader.style.display = "none";

        notyf.error('An error occurred. Please try again.');
    }
});

document.getElementById('unlockSavingsForm').addEventListener('submit', async (e) => {
    e.preventDefault();

    const form = e.target;

    const formData = new FormData(form);
    const loader = document.getElementById("loader");
    loader.style.display = "block";
    try {
        const response = await fetch("/savings/activate", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            },
            body: formData,
        });

        loader.style.display = "none";

        const result = await response.json();

        if (result.success) {
            notyf.success("Payment initiated successfully");
            const modalElement = document.getElementById('unlockSavingsModal');
            const modal = bootstrap.Modal.getInstance(modalElement);
            if (modal) {
                modal.hide();
            }
            setTimeout(() => {
                window.location.reload();
            }, 3000);
        } else {
            notyf.error(result.message || 'An error occurred.');
        }
    } catch (err) {
        loader.style.display = "none";

        notyf.error('An error occurred. Please try again.');
    }
});