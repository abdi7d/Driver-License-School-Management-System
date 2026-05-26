// Ensure a minimal global `api` exists immediately so pages can call `api.request`
if (!window.api) {
    window.api = {
        baseUrl: (function(){
            try {
                const path = window.location.pathname;
                const projectRoot = path.includes('/client/') ? path.substring(0, path.lastIndexOf('/client')) : '';
                return `${window.location.origin}${projectRoot}/server/api`;
            } catch (e) {
                return 'http://localhost/Driver-License-School/server/api';
            }
        })(),
        async request(endpoint, options = {}) {
            try {
                const token = (window.auth && window.auth.getToken) ? window.auth.getToken() : null;
                const headers = { ...(options.headers || {}) };
                if (!(options.body instanceof FormData) && options.body && typeof options.body !== 'string') {
                    headers['Content-Type'] = 'application/json';
                    options.body = JSON.stringify(options.body);
                }
                if (token) headers['Authorization'] = `Bearer ${token}`;
                const resp = await fetch(`${this.baseUrl}${endpoint}`, { ...options, headers });
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                return await resp.json();
            } catch (err) {
                console.error('[api fallback] request error', err);
                return { success: false, message: err.message };
            }
        }
    };
}

try {
const api = {
    // DEMO_MODE removed for production
    baseUrl: (() => {
        const path = window.location.pathname;
        const isFileProtocol = window.location.protocol === 'file:' || window.location.origin === 'null';

        if (isFileProtocol) {
            return 'http://localhost/Driver-License-School/server/api';
        }

        const projectRoot = path.includes('/client/')
            ? path.substring(0, path.lastIndexOf('/client'))
            : '';

        return `${window.location.origin}${projectRoot}/server/api`;
    })(),

    async request(endpoint, options = {}) {
        try {
            const token = (typeof auth !== 'undefined' && auth.getToken) ? auth.getToken() : localStorage.getItem('token');
            const headers = {
                ...options.headers
            };

            // Don't set Content-Type if body is FormData (browser will set it with boundary)
            if (!(options.body instanceof FormData)) {
                headers['Content-Type'] = 'application/json';
            }

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const url = endpoint.startsWith('http') ? endpoint : `${this.baseUrl}${endpoint}`;
            const response = await fetch(url, {
                ...options,
                headers
            });
            let data;
            try {
                data = await response.json();
            } catch (parseErr) {
                throw new Error(`Invalid JSON response (HTTP ${response.status})`);
            }

            if (!response.ok) {
                // Normalize error payload
                const message = data && (data.message || data.error) ? (data.message || data.error) : `${response.status} ${response.statusText}`;
                return { success: false, message };
            }
            
            // Standardize response if it only contains an error field
            if (data && data.error && data.success === undefined) {
                data.success = false;
                data.message = data.error;
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            return {
                success: false,
                message: error.message || 'Network error. Please check if the server is running.',
                error: error.name || 'Error'
            };
        }
    },
    
    async get(endpoint, options = {}) {
        return this.request(endpoint, { ...options, method: 'GET' });
    },
    
    async post(endpoint, body, options = {}) {
        return this.request(endpoint, { 
            ...options, 
            method: 'POST', 
            body: body instanceof FormData ? body : JSON.stringify(body) 
        });
    },
    
    async put(endpoint, body, options = {}) {
        return this.request(endpoint, { 
            ...options, 
            method: 'PUT', 
            body: body instanceof FormData ? body : JSON.stringify(body) 
        });
    },
    
    async delete(endpoint, options = {}) {
        return this.request(endpoint, { ...options, method: 'DELETE' });
    },

    mockRequest(endpoint, options) {
        return new Promise((resolve) => {
            setTimeout(() => {
                const user = auth.getCurrentUser();
                
                // Profile endpoints
                if (endpoint.includes('/profile') && options.method === 'GET') {
                    resolve({
                        success: true,
                        data: {
                            id: user.id,
                            name: user.name,
                            email: user.email,
                            phone: user.phone || '+251 911 234 567',
                            national_id: user.national_id || 'ET-123456789',
                            date_of_birth: user.date_of_birth || '1995-05-15',
                            region: user.region || 'Addis Ababa',
                            city: user.city || 'Addis Ababa',
                            license_class: user.license_class || 'B',
                            enrollment_date: user.enrollment_date || '2026-01-15',
                            status: user.status || 'active'
                        }
                    });
                } else if (endpoint.includes('/profile') && options.method === 'PUT') {
                    resolve({
                        success: true,
                        message: 'Profile updated successfully'
                        });
                    } else if (endpoint.includes('/notifications')) {
                        // Student notifications
                        resolve({
                            success: true,
                            data: [
                                { id: 1, type: 'lesson', icon: '📚', title: 'Upcoming Lesson', message: 'You have a practical lesson tomorrow at 09:00 AM with Instructor Yonas.', time: '2 hours ago', read: false },
                                { id: 2, type: 'exam', icon: '📝', title: 'Exam Scheduled', message: 'Your theory exam is scheduled for May 10, 2026 at 10:00 AM.', time: '1 day ago', read: false },
                                { id: 3, type: 'progress', icon: '📊', title: 'Progress Update', message: 'Great job! You\'ve completed 75% of your training program.', time: '2 days ago', read: true },
                                { id: 4, type: 'payment', icon: '💳', title: 'Payment Reminder', message: 'Your next payment of ETB 1,500 is due on May 01, 2026.', time: '3 days ago', read: true }
                            ]
                        });
                    } else if (endpoint.includes('/reports')) {
                        resolve({
                            success: true,
                            data: {
                                total_students: 87,
                                pass_rate: 80,
                                active_instructors: 4,
                                total_revenue: 357000,
                                programs: [
                                    { name: 'Motorcycle (A)', enrolled: 14, active: 10, completed: 3, dropped: 1, rate: 70, price: 2500 },
                                    { name: 'Private Car (B)', enrolled: 32, active: 22, completed: 8, dropped: 2, rate: 88, price: 3500 },
                                    { name: 'Heavy Truck (C)', enrolled: 18, active: 14, completed: 3, dropped: 1, rate: 78, price: 5500 },
                                    { name: 'Bus (D)', enrolled: 15, active: 11, completed: 3, dropped: 1, rate: 73, price: 5000 },
                                    { name: 'Transport (E)', enrolled: 8, active: 5, completed: 2, dropped: 1, rate: 63, price: 4500 }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/certificates')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, cert_no: 'CERT-2026-001', student: 'Hana Bekele', program: 'Private Car (B)', score: 92, date: '2026-04-15', status: 'pending' },
                                { id: 2, cert_no: 'CERT-2026-002', student: 'Meron Alemu', program: 'Transport (E)', score: 87, date: '2026-04-16', status: 'pending' },
                                { id: 3, cert_no: 'CERT-2026-003', student: 'Samuel Girma', program: 'Motorcycle (A)', score: 80, date: '2026-04-18', status: 'pending' }
                            ]
                        });
                    } else if (endpoint.includes('/approvals')) {
                        resolve({
                            success: true,
                            data: {
                                pending_registrations: 3,
                                pending_exams: 2,
                                pending_certificates: 1,
                                approved_today: 5
                            }
                        });
                    } else if (endpoint.includes('/profile')) {
                        resolve({
                            success: true,
                            data: {
                                id: user.id,
                                name: user.name || 'Manager',
                                email: user.email || 'manager@dlsm.et',
                                phone: '+251 911 000 001',
                                employee_id: 'MGR-001',
                                department: 'Administration',
                                member_since: 'January 2024',
                                status: 'active'
                            }
                        });
                    } else if (endpoint.includes('/audit-logs')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, timestamp: '2026-04-27 14:30:22', user: 'Manager Admin', role: 'manager', action: 'approve', details: 'Approved student registration for Abebe Kebede', ip: '192.168.1.100' },
                                { id: 2, timestamp: '2026-04-27 14:15:10', user: 'Instructor Yonas', role: 'instructor', action: 'update', details: 'Updated attendance for Elena Rodriguez', ip: '192.168.1.105' },
                                { id: 3, timestamp: '2026-04-27 13:45:33', user: 'Student Demo', role: 'student', action: 'login', details: 'User logged in successfully', ip: '192.168.1.120' },
                                { id: 4, timestamp: '2026-04-27 12:20:15', user: 'Manager Admin', role: 'manager', action: 'create', details: 'Created new program: Heavy Truck Advanced', ip: '192.168.1.100' },
                                { id: 5, timestamp: '2026-04-27 11:30:45', user: 'Supervisor Demo', role: 'supervisor', action: 'approve', details: 'Approved exam eligibility for Tigist Haile', ip: '192.168.1.110' },
                                { id: 6, timestamp: '2026-04-27 10:15:20', user: 'Manager Admin', role: 'manager', action: 'delete', details: 'Deleted inactive user account', ip: '192.168.1.100' },
                                { id: 7, timestamp: '2026-04-27 09:00:00', user: 'Instructor Liya', role: 'instructor', action: 'create', details: 'Created new training session', ip: '192.168.1.106' },
                                { id: 8, timestamp: '2026-04-26 16:45:30', user: 'Manager Admin', role: 'manager', action: 'approve', details: 'Approved certificate for Samuel Girma', ip: '192.168.1.100' }
                            ]
                        });
                    }
                }
                
                // Supervisor endpoints
                else if (endpoint.includes('/supervisor')) {
                    if (endpoint.includes('/dashboard') || endpoint.includes('/stats')) {
                        resolve({
                            success: true,
                            data: {
                                stats: {
                                    active_instructors: 7,
                                    total_students: 124,
                                    pending_approvals: 12,
                                    open_complaints: 3
                                },
                                recent_approvals: [
                                    { id: 1, student_name: 'Abebe Kebede', exam_type: 'Theory', student_user_id: 101 },
                                    { id: 2, student_name: 'Tigist Haile', exam_type: 'Practical', student_user_id: 102 }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/assignments')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, instructor: 'Instructor Yonas', student: 'Sarah Johnson', program: 'Private Car', date: '2026-04-25', status: 'pending' },
                                { id: 2, instructor: 'Instructor Liya', student: 'Michael Chen', program: 'Motorcycle', date: '2026-04-24', status: 'approved' },
                                { id: 3, instructor: 'Instructor Bekele', student: 'Lisa Anderson', program: 'Heavy Truck', date: '2026-04-23', status: 'pending' }
                            ]
                        });
                    } else if (endpoint.includes('/monitor')) {
                        resolve({
                            success: true,
                            data: {
                                active_sessions: 8,
                                instructors_online: 12,
                                todays_sessions: 24,
                                completed_today: 16,
                                sessions: [
                                    { id: 1, type: 'Practical', program: 'Private Car (B)', instructor: 'Dawit Tesfaye', student: 'Abebe Kebede', started: '09:30 AM', duration: '45 min', status: 'LIVE' },
                                    { id: 2, type: 'Theory', program: 'Motorcycle (A)', instructor: 'Meron Alemu', students: '12 Students', started: '10:00 AM', duration: '60 min', status: 'LIVE' },
                                    { id: 3, type: 'Practical', program: 'Heavy Truck (C)', instructor: 'Yonas Bekele', student: 'Samuel Girma', started: '10:15 AM', duration: '90 min', status: 'LIVE' }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/exam-approvals')) {
                        resolve({
                            success: true,
                            data: {
                                pending_approvals: 12,
                                approved_this_week: 28,
                                rejected_this_week: 4,
                                approval_rate: 87,
                                requests: [
                                    { id: 1, student: 'Abebe Kebede', program: 'Private Car (B)', exam_type: 'Theory', attendance: '95%', practice_tests: '88%', status: 'pending' },
                                    { id: 2, student: 'Tigist Haile', program: 'Motorcycle (A)', exam_type: 'Practical', practical_hours: '18/15', skills: 'Excellent', status: 'pending' },
                                    { id: 3, student: 'Samuel Girma', program: 'Heavy Truck (C)', exam_type: 'Practical', practical_hours: '22/25', skills: 'Good', status: 'pending' }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/complaints')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, student: 'Abebe Kebede', instructor: 'Instructor Yonas', type: 'Service Quality', date: '2026-04-25', status: 'pending', priority: 'high' },
                                { id: 2, student: 'Tigist Haile', instructor: 'Instructor Liya', type: 'Schedule Conflict', date: '2026-04-24', status: 'in_progress', priority: 'medium' },
                                { id: 3, student: 'Samuel Girma', instructor: 'Instructor Bekele', type: 'Equipment Issue', date: '2026-04-23', status: 'resolved', priority: 'low' },
                                { id: 4, student: 'Hana Bekele', instructor: 'Instructor Marta', type: 'Communication', date: '2026-04-22', status: 'resolved', priority: 'medium' }
                            ]
                        });
                    } else if (endpoint.includes('/reports')) {
                        resolve({
                            success: true,
                            data: {
                                total_instructors: 8,
                                active_instructors: 7,
                                total_sessions: 156,
                                completed_sessions: 142,
                                average_rating: 4.6,
                                complaints_resolved: 12,
                                instructors: [
                                    { name: 'Instructor Yonas', sessions: 45, rating: 4.8, students: 12, completion: 95 },
                                    { name: 'Instructor Liya', sessions: 38, rating: 4.7, students: 10, completion: 92 },
                                    { name: 'Instructor Bekele', sessions: 32, rating: 4.5, students: 8, completion: 88 },
                                    { name: 'Instructor Marta', sessions: 27, rating: 4.4, students: 7, completion: 85 }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/profile')) {
                        resolve({
                            success: true,
                            data: {
                                id: user.id,
                                name: user.name || 'Supervisor',
                                email: user.email || 'supervisor@dlsm.et',
                                phone: '+251 91 234 5678',
                                employee_id: 'SUP-2024-001',
                                department: 'Training Supervision',
                                member_since: 'April 2024',
                                status: 'active'
                            }
                        });
                    } else if (endpoint.includes('/messages')) {
                        resolve({
                            success: true,
                            message: 'Message operation successful'
                        });
                    }
                }
                
                // Student messaging endpoints
                else if (endpoint.includes('/student') && endpoint.includes('/messages')) {
                    if (endpoint.includes('/conversations')) {
                        resolve({
                            success: true,
                            data: []
                        });
                    } else if (endpoint.includes('/send') && options.method === 'POST') {
                        resolve({
                            success: true,
                            message: 'Message sent successfully'
                        });
                    }
                }
                
                // Instructor messaging endpoints
                else if (endpoint.includes('/instructor') && endpoint.includes('/messages')) {
                    if (endpoint.includes('/conversations')) {
                        resolve({
                            success: true,
                            data: []
                        });
                    } else if (endpoint.includes('/send') && options.method === 'POST') {
                        resolve({
                            success: true,
                            message: 'Message sent successfully'
                        });
                    }
                }
                
                // Supervisor messaging endpoints
                else if (endpoint.includes('/supervisor') && endpoint.includes('/messages')) {
                    if (endpoint.includes('/conversations')) {
                        resolve({
                            success: true,
                            data: []
                        });
                    } else if (endpoint.includes('/send') && options.method === 'POST') {
                        resolve({
                            success: true,
                            message: 'Message sent successfully'
                        });
                    }
                }
                
                // PAYMENT & BILLING ENDPOINTS
                // Finance user endpoints
                else if (endpoint.includes('/finance')) {
                    if (endpoint.includes('/pending-verifications')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, student: 'Sarah Johnson', program: 'Private Car (B)', amount: 2600, date: '2026-04-25', transaction_id: 'TXN-2026-ABC123', proof_url: 'proof-1.jpg', status: 'pending', notes: '' },
                                { id: 2, student: 'Robert Wilson', program: 'Private Car (B)', amount: 2600, date: '2026-04-24', transaction_id: 'TXN-2026-DEF456', proof_url: 'proof-2.jpg', status: 'pending', notes: '' },
                                { id: 3, student: 'Emma Wright', program: 'Bus (D)', amount: 2500, date: '2026-04-23', transaction_id: 'TXN-2026-GHI789', proof_url: 'proof-3.jpg', status: 'pending', notes: '' }
                            ]
                        });
                    } else if (endpoint.includes('/verify-payment') && options.method === 'POST') {
                        resolve({
                            success: true,
                            message: 'Payment verified successfully',
                            data: {
                                payment_id: options.body ? JSON.parse(options.body).payment_id : 0,
                                status: 'verified',
                                verified_by: 'Finance Officer',
                                verified_date: new Date().toISOString().split('T')[0]
                            }
                        });
                    } else if (endpoint.includes('/reject-payment') && options.method === 'POST') {
                        resolve({
                            success: true,
                            message: 'Payment rejected',
                            data: {
                                payment_id: options.body ? JSON.parse(options.body).payment_id : 0,
                                status: 'rejected',
                                reason: options.body ? JSON.parse(options.body).reason : 'Invalid proof'
                            }
                        });
                    } else if (endpoint.includes('/verified-payments')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, student: 'Michael Chen', program: 'Motorcycle (A)', amount: 1750, date: '2026-04-20', transaction_id: 'TXN-2026-XYZ111', verified_date: '2026-04-20', verified_by: 'Finance Officer', status: 'verified' },
                                { id: 2, student: 'Lisa Anderson', program: 'Heavy Truck (C)', amount: 2750, date: '2026-04-19', transaction_id: 'TXN-2026-XYZ222', verified_date: '2026-04-19', verified_by: 'Finance Officer', status: 'verified' }
                            ]
                        });
                    } else if (endpoint.includes('/dashboard')) {
                        resolve({
                            success: true,
                            data: {
                                pending_verifications: 3,
                                verified_today: 5,
                                rejected_today: 1,
                                total_verified: 45,
                                total_amount_verified: 156000
                            }
                        });
                    }
                }
                
                // Student payment endpoints
                else if (endpoint.includes('/student') && endpoint.includes('/payments')) {
                    if (endpoint.includes('/history')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, date: '2026-04-20', amount: 2600, method: 'Bank Transfer', status: 'completed', receipt: 'RCP-2026-001', description: 'First Installment - Private Car (B)' },
                                { id: 2, date: '2026-04-15', amount: 2600, method: 'Mobile Money', status: 'completed', receipt: 'RCP-2026-002', description: 'Second Installment - Private Car (B)' }
                            ]
                        });
                    } else if (endpoint.includes('/pending')) {
                        resolve({
                            success: true,
                            data: {
                                total_due: 1500,
                                due_date: '2026-05-01',
                                program: 'Private Car (Level 2)',
                                program_fee: 5200,
                                paid_amount: 3700,
                                remaining_amount: 1500,
                                payment_plan: [
                                    { installment: 1, amount: 2600, due_date: '2026-04-20', status: 'paid' },
                                    { installment: 2, amount: 2600, due_date: '2026-05-01', status: 'pending' }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/receipt/')) {
                        const receiptId = endpoint.split('/').pop();
                        resolve({
                            success: true,
                            data: {
                                receipt_number: receiptId,
                                student_name: user.name,
                                student_id: user.id,
                                program: 'Private Car (Level 2)',
                                amount: 2600,
                                payment_date: '2026-04-20',
                                payment_method: 'Bank Transfer',
                                transaction_id: 'TXN-2026-' + Math.random().toString(36).substr(2, 9).toUpperCase(),
                                status: 'completed',
                                issued_by: 'Manager Abebe Tadesse'
                            }
                        });
                    } else if (endpoint.includes('/make-payment') && options.method === 'POST') {
                        resolve({
                            success: true,
                            message: 'Payment submitted for verification',
                            data: {
                                payment_id: Math.floor(Math.random() * 10000),
                                receipt_number: 'RCP-2026-' + Math.random().toString(36).substr(2, 5).toUpperCase(),
                                amount: options.body ? JSON.parse(options.body).amount : 0,
                                status: 'pending_verification',
                                transaction_id: 'TXN-2026-' + Math.random().toString(36).substr(2, 9).toUpperCase(),
                                message: 'Payment proof submitted. Awaiting finance verification.'
                            }
                        });
                    }
                }
                
                // Manager billing endpoints
                else if (endpoint.includes('/manager') && endpoint.includes('/billing')) {
                    if (endpoint.includes('/dashboard')) {
                        resolve({
                            success: true,
                            data: {
                                total_revenue: 357000,
                                revenue_this_month: 45600,
                                pending_payments: 18500,
                                total_students: 87,
                                paid_students: 72,
                                pending_students: 15,
                                payment_methods: {
                                    'Bank Transfer': 156000,
                                    'Mobile Money': 145000,
                                    'Cash': 56000
                                },
                                revenue_by_program: [
                                    { program: 'Motorcycle (A)', revenue: 35000, students: 14, avg_payment: 2500 },
                                    { program: 'Private Car (B)', revenue: 112000, students: 32, avg_payment: 3500 },
                                    { program: 'Heavy Truck (C)', revenue: 99000, students: 18, avg_payment: 5500 },
                                    { program: 'Bus (D)', revenue: 75000, students: 15, avg_payment: 5000 },
                                    { program: 'Transport (E)', revenue: 36000, students: 8, avg_payment: 4500 }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/payments')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, student: 'Sarah Johnson', program: 'Private Car (B)', amount: 2600, date: '2026-04-25', method: 'Bank Transfer', status: 'completed', receipt: 'RCP-2026-001' },
                                { id: 2, student: 'Michael Chen', program: 'Motorcycle (A)', amount: 1750, date: '2026-04-24', method: 'Mobile Money', status: 'completed', receipt: 'RCP-2026-002' },
                                { id: 3, student: 'Lisa Anderson', program: 'Heavy Truck (C)', amount: 2750, date: '2026-04-23', method: 'Cash', status: 'completed', receipt: 'RCP-2026-003' },
                                { id: 4, student: 'Robert Wilson', program: 'Private Car (B)', amount: 2600, date: '2026-04-22', method: 'Bank Transfer', status: 'pending', receipt: 'RCP-2026-004' },
                                { id: 5, student: 'Emma Wright', program: 'Bus (D)', amount: 2500, date: '2026-04-21', method: 'Mobile Money', status: 'completed', receipt: 'RCP-2026-005' }
                            ]
                        });
                    } else if (endpoint.includes('/invoices')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, student: 'Sarah Johnson', program: 'Private Car (B)', total_amount: 5200, paid_amount: 2600, remaining: 2600, due_date: '2026-05-01', status: 'partial' },
                                { id: 2, student: 'Michael Chen', program: 'Motorcycle (A)', total_amount: 3500, paid_amount: 3500, remaining: 0, due_date: '2026-04-20', status: 'paid' },
                                { id: 3, student: 'Lisa Anderson', program: 'Heavy Truck (C)', total_amount: 8500, paid_amount: 2750, remaining: 5750, due_date: '2026-05-15', status: 'partial' },
                                { id: 4, student: 'Robert Wilson', program: 'Private Car (B)', total_amount: 5200, paid_amount: 0, remaining: 5200, due_date: '2026-05-01', status: 'pending' }
                            ]
                        });
                    } else if (endpoint.includes('/reports')) {
                        resolve({
                            success: true,
                            data: {
                                total_revenue: 357000,
                                total_students: 87,
                                average_payment: 4103,
                                payment_completion_rate: 82.7,
                                monthly_revenue: [
                                    { month: 'January', revenue: 42000 },
                                    { month: 'February', revenue: 48000 },
                                    { month: 'March', revenue: 52000 },
                                    { month: 'April', revenue: 45600 }
                                ],
                                payment_methods_breakdown: [
                                    { method: 'Bank Transfer', amount: 156000, percentage: 43.7 },
                                    { method: 'Mobile Money', amount: 145000, percentage: 40.6 },
                                    { method: 'Cash', amount: 56000, percentage: 15.7 }
                                ]
                            }
                        });
                    } else if (endpoint.includes('/pending-payments')) {
                        resolve({
                            success: true,
                            data: [
                                { id: 1, student: 'Robert Wilson', program: 'Private Car (B)', amount: 5200, due_date: '2026-05-01', days_overdue: 0, status: 'pending' },
                                { id: 2, student: 'Tigist Haile', program: 'Heavy Truck (C)', amount: 3000, due_date: '2026-04-28', days_overdue: 2, status: 'overdue' },
                                { id: 3, student: 'Abebe Kebede', program: 'Bus (D)', amount: 2500, due_date: '2026-04-25', days_overdue: 5, status: 'overdue' }
                            ]
                        });
                    }
                }
            }, 300);
        });
    },

    async get(endpoint) {
        return this.request(endpoint, { method: 'GET' });
    },

    async post(endpoint, data) {
        return this.request(endpoint, {
            method: 'POST',
            body: JSON.stringify(data)
        });
    },

    async put(endpoint, data) {
        return this.request(endpoint, {
            method: 'PUT',
            body: JSON.stringify(data)
        });
    },

    async delete(endpoint) {
        return this.request(endpoint, { method: 'DELETE' });
    },

    // File upload method
    async upload(endpoint, formData) {
        try {
            const token = auth.getToken();
            const headers = {};

            if (token) {
                headers['Authorization'] = `Bearer ${token}`;
            }

            const response = await fetch(`${this.baseUrl}${endpoint}`, {
                method: 'POST',
                headers,
                body: formData
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            return await response.json();
        } catch (error) {
            console.error('Upload Error:', error);
            return {
                success: false,
                message: error.message || 'Upload failed',
                error: error.name
            };
        }
    }
};

// Global API instance for backward compatibility
window.api = api;
} catch (error) {
    console.error('[API] Error creating api object:', error);
    // Provide a minimal fallback API so pages don't break if api.js initialization fails
    const fallbackBase = (function(){
        try {
            const path = window.location.pathname;
            const projectRoot = path.includes('/client/') ? path.substring(0, path.lastIndexOf('/client')) : '';
            return `${window.location.origin}${projectRoot}/server/api`;
        } catch (e) {
            return 'http://localhost/Driver-License-School/server/api';
        }
    })();

    const fallbackApi = {
        baseUrl: fallbackBase,
        async request(endpoint, options = {}) {
            try {
                const url = `${this.baseUrl}${endpoint}`;
                const opts = { ...options };
                if (opts.body && !(opts.body instanceof FormData) && typeof opts.body !== 'string') {
                    opts.body = JSON.stringify(opts.body);
                    opts.headers = { ...(opts.headers||{}), 'Content-Type': 'application/json' };
                }
                const resp = await fetch(url, opts);
                if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
                return await resp.json();
            } catch (err) {
                console.error('[API fallback] request error', err);
                return { success: false, message: err.message };
            }
        },
        get(endpoint, opts) { return this.request(endpoint, { ...(opts||{}), method: 'GET' }); },
        post(endpoint, body, opts) { return this.request(endpoint, { ...(opts||{}), method: 'POST', body }); },
        put(endpoint, body, opts) { return this.request(endpoint, { ...(opts||{}), method: 'PUT', body }); },
        delete(endpoint, opts) { return this.request(endpoint, { ...(opts||{}), method: 'DELETE' }); },
        upload(endpoint, formData) { return this.request(endpoint, { method: 'POST', body: formData }); }
    };

    window.api = fallbackApi;
}