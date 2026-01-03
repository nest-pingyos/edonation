<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "โครงการทั้งหมด";
$pageDesc = "รายการโครงการบริจาคทั้งหมดจากคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<!-- Projects Page Styles -->
<link rel="stylesheet" href="../assets/css/projects.css">

<body>
    <div class="wrapper">
        <?php include_once('../config/header.php'); ?>

        <section class="projects-section">
            <div class="container">
                <!-- Header -->
                <div class="projects-header">
                    <h1>โครงการบริจาค</h1>
                    <p>ร่วมบริจาคกับคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่ เพื่อสนับสนุนการศึกษาและพัฒนาวิชาชีพพยาบาล</p>
                </div>

                <!-- Loading State -->
                <div id="loadingState" class="projects-loading">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Loading...</span>
                    </div>
                    <p class="mt-3">กำลังโหลดข้อมูล...</p>
                </div>

                <!-- Empty State -->
                <div id="emptyState" class="projects-empty" style="display: none;">
                    <h4>ไม่พบโครงการ</h4>
                    <p>ยังไม่มีโครงการในระบบขณะนี้</p>
                </div>

                <!-- Projects Grid -->
                <div id="projectsGrid" class="projects-grid" style="display: none;">
                    <!-- Cards will be loaded via JavaScript -->
                </div>
            </div>
        </section>

        <?php include_once('../config/footer.php'); ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/plugins.js"></script>
    <script src="../assets/js/main.js"></script>

    <script>
        // Get API_BASE from meta tag (set by PHP head.php)
        const API_BASE = document.querySelector('meta[name="api-base"]')?.content || '/edonation/api/v1';

        document.addEventListener('DOMContentLoaded', function () {
            loadProjects();
        });

        async function loadProjects() {
            const loadingState = document.getElementById('loadingState');
            const emptyState = document.getElementById('emptyState');
            const projectsGrid = document.getElementById('projectsGrid');

            try {
                const response = await fetch(`${API_BASE}/projects?status=active`);
                const result = await response.json();

                loadingState.style.display = 'none';

                if (result.success && result.data && result.data.length > 0) {
                    projectsGrid.style.display = 'grid';
                    renderProjects(result.data);
                } else {
                    emptyState.style.display = 'block';
                }
            } catch (error) {
                console.error('Error loading projects:', error);
                loadingState.style.display = 'none';
                emptyState.innerHTML = `
                <h4>เกิดข้อผิดพลาด</h4>
                <p>ไม่สามารถโหลดข้อมูลได้ กรุณาลองใหม่อีกครั้ง</p>
            `;
                emptyState.style.display = 'block';
            }
        }

        function renderProjects(projects) {
            const projectsGrid = document.getElementById('projectsGrid');

            // Get lang parameter
            const urlParams = new URLSearchParams(window.location.search);
            const lang = urlParams.get('lang');
            const langParam = lang ? `?lang=${lang}` : '';

            let html = '';
            projects.forEach(project => {
                html += `
                <div class="project-card">
                    <div class="project-card-image">
                        <img src="${project.image_url}" alt="${escapeHtml(project.project_name || 'Project')}" 
                             onerror="this.src='../assets/images/projects/pro-1.jpg'">
                    </div>
                    <div class="project-card-content">
                        <h3 class="project-card-title">${escapeHtml(project.project_name_web || project.project_name || 'โครงการ')}</h3>
                        <p class="project-card-desc">${escapeHtml(project.description || project.short_description || project.project_description || project.project_tex || 'รายละเอียดโครงการ...')}</p>
                        <div class="project-card-footer">
                            <a href="../donat/${project.project_number}${langParam}" class="btn-donate">
                                <i class="fas fa-heart"></i>
                                <span>บริจาคเลย</span>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
            `;
            });

            projectsGrid.innerHTML = html;
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
    </script>
</body>

</html>