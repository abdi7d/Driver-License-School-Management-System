document.addEventListener('DOMContentLoaded', async function() {
    try {
        // Check if required dependencies are loaded
        if (typeof dashboard === 'undefined') {
            console.error('❌ Dashboard module not loaded');
            return;
        }
        
        if (typeof auth === 'undefined') {
            console.error('❌ Auth module not loaded');
            return;
        }
        
        const user = dashboard.init({ requiredRole: 'student' });
        if (!user) return;

        const userNameEl = document.getElementById('userName');
        if (userNameEl) {
            userNameEl.textContent = user.name || 'Student';
        }

        await loadCertificateStatus(user);

        const dlBtn = document.getElementById('downloadBtn');
        if (dlBtn) {
            dlBtn.addEventListener('click', function() {
                downloadCertificate(user);
            });
        }

        console.log('✅ Certificate page initialized successfully');
    } catch (error) {
        console.error('❌ Certificate page initialization error:', error);
        // Don't show notification to avoid the error message
    }
});

async function loadCertificateStatus(user) {
    try {
        const CERTIFICATE_PROGRESS_THRESHOLD = 80;
        const progressResponse = await api.get('/progress');
        
        if (progressResponse.success && progressResponse.data) {
            const progress = progressResponse.data;
            
            const isEligible = progress.progress_percentage >= CERTIFICATE_PROGRESS_THRESHOLD;
            
            if (isEligible) {
                const certResponse = await api.get('/student/certificates');
                
                if (certResponse.success && certResponse.data) {
                    showCertificate(user, certResponse.data);
                } else {
                    showPendingCertificate(user);
                }
            } else {
                showLockedCertificate(progress);
            }
            
            console.log('Certificate status loaded:', { isEligible, progress: progress.progress_percentage + '%' });
        } else {
            // Fallback to locked state if API fails
            showLockedCertificate({ progress_percentage: 0 });
        }
    } catch (error) {
        console.error('Error loading certificate status:', error);
        showLockedCertificate({ progress_percentage: 0 });
        // Only show notification if utils is available
        if (typeof utils !== 'undefined' && utils.showNotification) {
            utils.showNotification('Error loading certificate status', 'error');
        }
    }
}

function showCertificate(user, certData) {
    document.getElementById('certLocked').style.display = 'none';
    document.getElementById('certEarned').style.display = 'block';
    
    document.getElementById('certName').textContent = user.name || 'Student Name';
    document.getElementById('certNationalId').textContent = certData.national_id || 'ET-123456789';
    document.getElementById('certDOB').textContent = certData.date_of_birth ? 
        new Date(certData.date_of_birth).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) : 
        'January 1, 2000';
    
    const licenseCategory = certData.license_category || 'B';
    const licenseTypes = {
        'A': 'Motorcycle License',
        'B': 'Private Car License',
        'C': 'Heavy Truck License',
        'D': 'Bus License',
        'E': 'Transportation License'
    };
    document.getElementById('certLicense').innerHTML = `
        <div class="cert-license-category">Category ${licenseCategory}</div>
        <div class="cert-license-type">${licenseTypes[licenseCategory] || 'Driver License'}</div>
    `;
    
    const startDate = certData.training_start ? new Date(certData.training_start) : new Date('2026-01-15');
    const endDate = certData.training_end ? new Date(certData.training_end) : new Date();
    document.getElementById('certPeriod').textContent = 
        `${startDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })} - ${endDate.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}`;
    
    document.getElementById('certHours').textContent = `${certData.total_hours || 40} hours`;
    document.getElementById('certTheoryScore').textContent = `${certData.theory_score || 85}% (Passed)`;
    document.getElementById('certPracticalScore').textContent = `${certData.practical_score || 90}% (Passed)`;
    
    const certNumber = certData.certificate_number || `DLSM-${new Date().getFullYear()}-${licenseCategory}-${String(Math.floor(Math.random() * 99999)).padStart(5, '0')}`;
    document.getElementById('certNumber').textContent = certNumber;
    
    const issueDate = certData.issue_date ? new Date(certData.issue_date) : new Date();
    document.getElementById('certDate').textContent = issueDate.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    const validUntil = new Date(issueDate);
    validUntil.setMonth(validUntil.getMonth() + 6);
    document.getElementById('certValidUntil').textContent = validUntil.toLocaleDateString('en-US', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
    
    document.getElementById('certInstructor').textContent = certData.instructor_name || 'Instructor Yonas Bekele';
    document.getElementById('certManager').textContent = certData.manager_name || 'Manager Abebe Tadesse';
    
    generateQRCode(certNumber);
}

function generateQRCode(certificateNumber) {
    try {
        const qrContainer = document.getElementById('certQRCode');
        if (qrContainer && typeof QRCode !== 'undefined') {
            qrContainer.innerHTML = '';
            
            // Get current path and create proper verification URL
            const currentPath = window.location.pathname;
            const isInStudentPortal = currentPath.includes('student-portal');
            
            let verifyUrl;
            if (isInStudentPortal) {
                // If in student-portal, go up one level
                verifyUrl = `${window.location.origin}${currentPath.substring(0, currentPath.lastIndexOf('/student-portal'))}/verify-certificate.html?cert=${certificateNumber}`;
            } else {
                // Otherwise use relative path
                const directory = currentPath.substring(0, currentPath.lastIndexOf('/'));
                verifyUrl = `${window.location.origin}${directory}/verify-certificate.html?cert=${certificateNumber}`;
            }
            
            console.log('QR Code URL:', verifyUrl);
            
            new QRCode(qrContainer, {
                text: verifyUrl,
                width: 100,
                height: 100,
                colorDark: "#1e293b",
                colorLight: "#ffffff",
                correctLevel: QRCode.CorrectLevel.H
            });
            
            console.log('✅ QR Code generated successfully');
        } else {
            console.warn('⚠️ QR Code library not available or container not found');
            if (qrContainer) {
                qrContainer.innerHTML = '<div style="width:100px;height:100px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;font-size:12px;text-align:center;">QR Code<br>Unavailable</div>';
            }
        }
    } catch (error) {
        console.error('Error generating QR code:', error);
        const qrContainer = document.getElementById('certQRCode');
        if (qrContainer) {
            qrContainer.innerHTML = '<div style="width:100px;height:100px;background:#fee2e2;display:flex;align-items:center;justify-content:center;font-size:12px;text-align:center;color:#dc2626;">QR Error</div>';
        }
    }
}

function showPendingCertificate() {
    const lockedDiv = document.getElementById('certLocked');
    const earnedDiv = document.getElementById('certEarned');
    
    if (lockedDiv && earnedDiv) {
        lockedDiv.style.display = 'none';
        earnedDiv.style.display = 'block';
        
        const certCard = earnedDiv.querySelector('.certificate-card');
        if (certCard) {
            certCard.innerHTML = `
                <div class="certificate-header">
                    <h2>🎓 Certificate Pending</h2>
                </div>
                <div class="certificate-body">
                    <p>Congratulations! You have completed all requirements.</p>
                    <p>Your certificate is being processed and will be available soon.</p>
                    <div class="cert-status">
                        <span class="status-badge pending">Processing</span>
                    </div>
                </div>
            `;
        }
    }
}

function showLockedCertificate(progress) {
    const lockedDiv = document.getElementById('certLocked');
    const earnedDiv = document.getElementById('certEarned');
    
    if (lockedDiv && earnedDiv) {
        lockedDiv.style.display = 'block';
        earnedDiv.style.display = 'none';
        
        const progressText = lockedDiv.querySelector('.cert-progress');
        if (progressText) {
            progressText.textContent = `Current Progress: ${progress.progress_percentage || 0}%`;
        }
        
        let progressBar = lockedDiv.querySelector('.cert-progress-bar');
        if (!progressBar) {
            progressBar = document.createElement('div');
            progressBar.className = 'cert-progress-bar';
            progressBar.innerHTML = `
                <div class="progress-track">
                    <div class="progress-fill" style="width: ${progress.progress_percentage || 0}%"></div>
                </div>
            `;
            const progressTextParent = progressText ? progressText.parentNode : lockedDiv;
            if (progressTextParent) {
                progressTextParent.insertBefore(progressBar, progressText ? progressText.nextSibling : null);
            }
        } else {
            const fill = progressBar.querySelector('.progress-fill');
            if (fill) {
                fill.style.width = (progress.progress_percentage || 0) + '%';
            }
        }
    }
}

function verifyCertificate() {
    const certNumber = document.getElementById('certNumber').textContent;
    // Keep URL consistent with QR-code generation for student-portal pages.
    const currentPath = window.location.pathname;
    const isInStudentPortal = currentPath.includes('student-portal');
    let verifyUrl;
    if (isInStudentPortal) {
        // Student portal pages live one folder deeper than verify-certificate.html
        verifyUrl = `${window.location.origin}/verify-certificate.html?cert=${encodeURIComponent(certNumber)}`;
    } else {
        const directory = currentPath.substring(0, currentPath.lastIndexOf('/'));
        verifyUrl = `${window.location.origin}${directory}/verify-certificate.html?cert=${encodeURIComponent(certNumber)}`;
    }
    window.open(verifyUrl, '_blank');
}

async function downloadCertificate() {
    try {
        utils.showNotification('Certificate download started! (Demo mode - PDF generation will be available when backend is connected)', 'success');
    } catch (error) {
        console.error('Download error:', error);
        utils.showNotification('Download failed. Please try again.', 'error');
    }
}
