/**
 * Grade Management System - Frontend Application
 * Institut Formation
 */

class GradeManagementApp {
    constructor() {
        this.csrfToken = this.getCsrfToken();
        this.currentUser = null;
        this.currentRole = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadUserSession();
    }

    getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }

    setupEventListeners() {
        // Login form
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (e) => this.handleLogin(e));
        }

        // Grade entry form
        const gradeForm = document.getElementById('gradeForm');
        if (gradeForm) {
            gradeForm.addEventListener('submit', (e) => this.handleGradeEntry(e));
        }

        // Module selector
        const moduleSelect = document.getElementById('moduleSelect');
        if (moduleSelect) {
            moduleSelect.addEventListener('change', (e) => this.onModuleSelected(e));
        }

        // Student profile update
        const profileForm = document.getElementById('profileForm');
        if (profileForm) {
            profileForm.addEventListener('submit', (e) => this.handleProfileUpdate(e));
        }

        // Export buttons
        document.querySelectorAll('[data-action="export-json"]').forEach(btn => {
            btn.addEventListener('click', () => this.exportToJSON());
        });

        document.querySelectorAll('[data-action="export-pdf"]').forEach(btn => {
            btn.addEventListener('click', () => this.exportToPDF());
        });
    }

    // Authentication
    async handleLogin(event) {
        event.preventDefault();

        const username = document.querySelector('input[name="username"]').value;
        const password = document.querySelector('input[name="password"]').value;

        const formData = new FormData();
        formData.append('username', username);
        formData.append('password', password);
        formData.append('csrf_token', this.csrfToken);

        try {
            const response = await fetch('/api/auth/login', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                window.location.href = '/dashboard';
            } else {
                this.showAlert('Invalid credentials', 'danger');
            }
        } catch (error) {
            console.error('Login error:', error);
            this.showAlert('Login failed', 'danger');
        }
    }

    async loadUserSession() {
        try {
            const response = await fetch('/api/user/session');
            if (response.ok) {
                const data = await response.json();
                this.currentUser = data.username;
                this.currentRole = data.role;
            }
        } catch (error) {
            console.error('Session load error:', error);
        }
    }

    // Grade Management
    async handleGradeEntry(event) {
        event.preventDefault();

        const studentId = document.querySelector('input[name="student_id"]').value;
        const moduleId = document.querySelector('select[name="module_id"]').value;
        const score = parseFloat(document.querySelector('input[name="score"]').value);

        // Validate score
        if (score < 0 || score > 20) {
            this.showAlert('Score must be between 0 and 20', 'danger');
            return;
        }

        // Check for eliminating grade
        const isEliminating = score <= 5;
        if (isEliminating) {
            this.showAlert('⚠️ Eliminating grade detected (≤5)', 'warning');
        }

        const formData = new FormData();
        formData.append('student_id', studentId);
        formData.append('module_id', moduleId);
        formData.append('score', score);
        formData.append('csrf_token', this.csrfToken);

        try {
            const response = await fetch('/api/grades/enter', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success || data.warning) {
                this.showAlert('Grade entered successfully', 'success');
                this.refreshGradeTable();
            } else {
                this.showAlert(data.error || 'Error entering grade', 'danger');
            }
        } catch (error) {
            console.error('Grade entry error:', error);
            this.showAlert('Failed to enter grade', 'danger');
        }
    }

    onModuleSelected(event) {
        const moduleId = event.target.value;
        // Fetch module details if needed
        console.log('Selected module:', moduleId);
    }

    // Student Profile
    async handleProfileUpdate(event) {
        event.preventDefault();

        const email = document.querySelector('input[name="email"]').value;
        const phone = document.querySelector('input[name="phone"]').value;

        // Validate email
        if (!this.validateEmail(email)) {
            this.showAlert('Invalid email address', 'danger');
            return;
        }

        // Validate phone
        if (!this.validatePhone(phone)) {
            this.showAlert('Invalid phone number', 'danger');
            return;
        }

        const formData = new FormData();
        formData.append('email', email);
        formData.append('phone', phone);
        formData.append('csrf_token', this.csrfToken);

        try {
            const response = await fetch('/api/students/profile/update', {
                method: 'POST',
                body: formData
            });

            const data = await response.json();

            if (data.success) {
                this.showAlert('Profile updated successfully', 'success');
            } else {
                this.showAlert(data.error || 'Error updating profile', 'danger');
            }
        } catch (error) {
            console.error('Profile update error:', error);
            this.showAlert('Failed to update profile', 'danger');
        }
    }

    // Reporting
    async generatePVDeliberation(cohort, group = null) {
        try {
            let url = `/api/reports/pv?cohort=${encodeURIComponent(cohort)}`;
            if (group) {
                url += `&group=${encodeURIComponent(group)}`;
            }

            const response = await fetch(url);
            const data = await response.json();

            if (data.data) {
                this.displayPVReport(data.data, cohort);
            } else {
                this.showAlert('Error generating PV', 'danger');
            }
        } catch (error) {
            console.error('PV generation error:', error);
            this.showAlert('Failed to generate PV', 'danger');
        }
    }

    displayPVReport(data, cohort) {
        const reportHTML = `
            <div class="report-container">
                <h2>PV de Délibération - ${cohort}</h2>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Code Apprenant</th>
                            <th>Moy 1A</th>
                            <th>Moy 2A</th>
                            <th>Stages</th>
                            <th>EFF</th>
                            <th>NGR Final</th>
                            <th>Décision</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${data.map(row => `
                            <tr>
                                <td>${this.sanitize(row.student_code)}</td>
                                <td>${row.avg_1a || '-'}</td>
                                <td>${row.avg_2a || '-'}</td>
                                <td>${row.stages || '-'}</td>
                                <td>${row.eff || '-'}</td>
                                <td><strong>${row.ngr_final || '-'}</strong></td>
                                <td>
                                    <span class="badge ${this.getDecisionClass(row.decision)}">
                                        ${row.decision || 'PENDING'}
                                    </span>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        `;

        this.openModal('PV de Délibération', reportHTML, 'large');
    }

    getDecisionClass(decision) {
        switch (decision) {
            case 'ADMIS':
                return 'badge-success';
            case 'AJOURNÉ':
                return 'badge-danger';
            case 'RATTRAPAGE':
                return 'badge-warning';
            default:
                return 'badge-primary';
        }
    }

    // Export Functions
    async exportToJSON() {
        try {
            const response = await fetch('/api/export/json');
            const data = await response.json();

            const jsonString = JSON.stringify(data, null, 2);
            const blob = new Blob([jsonString], { type: 'application/json' });
            this.downloadFile(blob, `grades_backup_${new Date().toISOString().split('T')[0]}.json`);

            this.showAlert('Data exported to JSON', 'success');
        } catch (error) {
            console.error('Export error:', error);
            this.showAlert('Export failed', 'danger');
        }
    }

    async exportToPDF() {
        try {
            const response = await fetch('/api/export/pdf');
            const blob = await response.blob();

            this.downloadFile(blob, `grades_report_${new Date().toISOString().split('T')[0]}.pdf`);
            this.showAlert('Report exported as PDF', 'success');
        } catch (error) {
            console.error('PDF export error:', error);
            this.showAlert('PDF export failed', 'danger');
        }
    }

    downloadFile(blob, filename) {
        const url = window.URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        window.URL.revokeObjectURL(url);
    }

    // UI Helpers
    openModal(title, content, size = 'normal') {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay active';
        modal.innerHTML = `
            <div class="modal ${size}">
                <div class="modal-header">
                    <h2 class="modal-title">${title}</h2>
                    <button class="modal-close" onclick="this.closest('.modal-overlay').remove()">×</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        `;

        document.body.appendChild(modal);

        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.remove();
            }
        });
    }

    showAlert(message, type = 'info') {
        const alert = document.createElement('div');
        alert.className = `alert alert-${type}`;
        alert.textContent = message;

        const container = document.getElementById('alertContainer') || document.body;
        container.insertBefore(alert, container.firstChild);

        setTimeout(() => alert.remove(), 5000);
    }

    refreshGradeTable() {
        const table = document.getElementById('gradeTable');
        if (table) {
            // Reload table data
            location.reload();
        }
    }

    // Validation
    validateEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    validatePhone(phone) {
        const phoneRegex = /^\+?[0-9\s\-\(\)]{7,}$/;
        return phoneRegex.test(phone);
    }

    sanitize(html) {
        const div = document.createElement('div');
        div.textContent = html;
        return div.innerHTML;
    }
}

// Initialize app when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    window.app = new GradeManagementApp();
});
