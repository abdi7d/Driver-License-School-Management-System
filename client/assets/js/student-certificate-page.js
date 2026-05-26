
document.addEventListener('DOMContentLoaded', async function() {
    const user = dashboard.init({ requiredRole: 'student' });
    if (!user) return;

    // Update topbar name
    const userNameEl = document.getElementById('userName');
    if (userNameEl) userNameEl.textContent = user.name || 'Student';

    await loadCertificateData();
});

async function loadCertificateData() {
    const certEarned = document.getElementById('certEarned');
    const certLocked = document.getElementById('certLocked');
    
    try {
        const response = await api.get('/students/certificate.php');
        if (response.success && response.data && response.data.length > 0) {
            const cert = response.data[0]; // Assuming one cert per student for now
            
            // Show earned section
            if (certEarned) certEarned.style.display = 'block';
            if (certLocked) certLocked.style.display = 'none';
            
            // Populate certificate data
            const user = auth.getCurrentUser();
            document.getElementById('certName').textContent = user ? user.name : 'Student';
            const certNumberEl = document.getElementById('certNumber');
            if (certNumberEl) certNumberEl.textContent = cert.certificate_number;

            const certDateEl = document.getElementById('certDate');
            if (certDateEl) certDateEl.textContent = new Date(cert.issue_date).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });

            // Some templates use 'certProgram' while others render a badge with id 'certLicense'.
            const certProgramEl = document.getElementById('certProgram') || document.getElementById('certLicense');
            if (certProgramEl) certProgramEl.textContent = cert.program_name || (cert.license_class ? 'Class ' + cert.license_class : 'Training Program');
            
            // Add valid until (6 months later)
            const issueDate = new Date(cert.issue_date);
            const validUntil = new Date(issueDate);
            validUntil.setMonth(validUntil.getMonth() + 6);
            document.getElementById('certValidUntil').textContent = validUntil.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
            
            // Instructor/Manager names from API if available
            if (cert.issued_by_name) {
                document.getElementById('certManager').textContent = `Manager ${cert.issued_by_name}`;
            }

            // Generate QR Code if library is available
            if (typeof QRCode !== 'undefined') {
                const qrContainer = document.getElementById('certQRCode');
                if (qrContainer) {
                    qrContainer.innerHTML = ''; // Clear previous
                    new QRCode(qrContainer, {
                        text: `${window.location.origin}/verify.html?cert=${cert.certificate_number}`,
                        width: 100,
                        height: 100
                    });
                }
            }

            // Update steps to all completed
            document.querySelectorAll('.cert-step-status').forEach(el => {
                el.className = 'cert-step-status done';
                el.textContent = '✓ Completed';
            });
            
        } else {
            // Show locked section
            if (certEarned) certEarned.style.display = 'none';
            if (certLocked) certLocked.style.display = 'block';
            
            // Fetch progress for the locked view
            await loadProgressSteps();
        }
    } catch (error) {
        console.error('Error loading certificate:', error);
        if (typeof utils !== 'undefined') {
            utils.showNotification('Failed to load certificate data', 'error');
        }
    }
}

async function loadProgressSteps() {
    try {
        const response = await api.get('/students/progress.php');
        if (response.success && response.data) {
            const progress = response.data.progress_percentage || 0;
            
            // Find the progress element and update it
            const lockedDiv = document.getElementById('certLocked');
            if (lockedDiv) {
                const progressText = lockedDiv.querySelector('.cert-progress');
                if (progressText) progressText.textContent = `Current Progress: ${progress}%`;
                
                let progressBar = lockedDiv.querySelector('.cert-progress-bar');
                if (!progressBar) {
                    progressBar = document.createElement('div');
                    progressBar.className = 'cert-progress-bar';
                    progressBar.innerHTML = `
                        <div class="progress-track">
                            <div class="progress-fill" style="width: ${progress}%"></div>
                        </div>
                    `;
                    if (progressText && progressText.parentNode) {
                        progressText.parentNode.insertBefore(progressBar, progressText.nextSibling);
                    }
                } else {
                    const fill = progressBar.querySelector('.progress-fill');
                    if (fill) fill.style.width = progress + '%';
                }
            }
        }
    } catch (error) {
        console.error('Error loading progress steps:', error);
    }
}

// Print functionality
function printCertificate() {
    const printWindow = window.open('', '', 'height=600,width=800');
    const certificateContent = document.getElementById('certificatePrint').innerHTML;

    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Certificate</title>
            <style>
                body { font-family: 'Inter', sans-serif; margin: 0; padding: 20px; }
                .certificate-card {
                    border: 3px solid #1E40AF;
                    border-radius: 12px;
                    padding: 40px;
                    text-align: center;
                    background: white;
                    max-width: 800px;
                    margin: 0 auto;
                }
                .cert-logo { font-size: 48px; margin-bottom: 20px; }
                .cert-school { font-size: 14px; color: #64748b; margin-bottom: 10px; }
                .cert-title-text { font-size: 32px; font-weight: 700; color: #1E40AF; margin: 20px 0; }
                .cert-subtitle { font-size: 14px; color: #64748b; margin: 10px 0; }
                .cert-student-name { font-size: 28px; font-weight: 700; color: #1e293b; margin: 20px 0; }
                .cert-body-text { font-size: 14px; color: #475569; line-height: 1.6; margin: 15px 0; }
                .cert-license-badge {
                    display: inline-block;
                    background: #1E40AF;
                    color: white;
                    padding: 10px 20px;
                    border-radius: 8px;
                    font-weight: 600;
                    margin: 15px 0;
                }
                .cert-footer-row {
                    display: flex;
                    justify-content: space-around;
                    margin-top: 40px;
                    padding-top: 20px;
                    border-top: 2px solid #e2e8f0;
                }
                .cert-footer-item { text-align: center; }
                .cert-footer-label { font-size: 12px; color: #64748b; margin-bottom: 5px; }
                .cert-footer-value { font-size: 14px; font-weight: 600; color: #1e293b; }
                @media print { body { margin: 0; padding: 0; } }
            </style>
        </head>
        <body>
            <div class="certificate-card">${certificateContent}</div>
        </body>
        </html>
    `);

    printWindow.document.close();
    printWindow.print();
}

// Download as PDF (using html2pdf library)
function downloadCertificatePDF() {
    const element = document.getElementById('certificatePrint');
    const studentName = document.getElementById('certName').textContent;

    if (typeof html2pdf === 'undefined') {
        if (typeof utils !== 'undefined') {
            utils.showNotification('PDF download requires html2pdf library. Using print dialog instead.', 'info');
        }
        printCertificate();
        return;
    }

    const opt = {
        margin: 10,
        filename: `Certificate_${studentName.replace(/\s+/g, '_')}.pdf`,
        image: { type: 'jpeg', quality: 0.98 },
        html2canvas: { scale: 2 },
        jsPDF: { orientation: 'portrait', unit: 'mm', format: 'a4' }
    };

    html2pdf().set(opt).from(element).save();
    if (typeof utils !== 'undefined') {
        utils.showNotification('Certificate downloaded successfully!', 'success');
    }
}

// Share certificate
function shareCertificate() {
    openModal('shareModal');
}

function shareVia(platform) {
    const certNumberEl = document.getElementById('certNumber');
    const certNumber = certNumberEl ? certNumberEl.textContent : 'N/A';
    const message = `I have successfully completed my driver training and earned my certificate! Certificate #${certNumber}`;

    switch (platform) {
        case 'email':
            window.location.href = `mailto:?subject=My Driver License Certificate&body=${encodeURIComponent(message)}`;
            break;
        case 'whatsapp':
            window.open(`https://wa.me/?text=${encodeURIComponent(message)}`, '_blank');
            break;
        case 'facebook':
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(window.location.href)}`, '_blank');
            break;
        case 'copy': {
            const text = `${message}\n\nCertificate Link: ${window.location.href}`;
            navigator.clipboard.writeText(text).then(() => {
                if (typeof utils !== 'undefined') {
                    utils.showNotification('Certificate link copied to clipboard!', 'success');
                }
                closeModal('shareModal');
            });
            break;
        }
    }
}

// Modal functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        modal.style.animation = 'fadeIn 0.3s ease-in-out';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'none';
    }
}

// Close modal when clicking outside
document.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.style.display = 'none';
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.style.display = 'none';
        });
    }
});
