<!DOCTYPE html>
<html lang="th">

<?php
$pageTitle = "โครงการทั้งหมด";
$pageDesc = "รายการโครงการบริจาคทั้งหมดจากคณะพยาบาลศาสตร์ มหาวิทยาลัยเชียงใหม่";
include_once('../config/head.php');
?>

<style>
    /* Projects Page Styles */
    .projects-section {
        padding: 80px 0;
        background: #ffffff;
        min-height: 100vh;
    }

    .projects-header {
        text-align: center;
        margin-bottom: 60px;
    }

    .projects-header h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #1a3a5c;
        margin-bottom: 15px;
    }

    .projects-header p {
        font-size: 1.1rem;
        color: #6c757d;
        max-width: 600px;
        margin: 0 auto;
    }

    /* Projects Grid */
    .projects-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 30px;
    }

    @media (max-width: 992px) {
        .projects-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .projects-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Project Card */
    .project-card {
        background: #ffffff;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .project-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.15);
    }

    /* Card Image */
    .project-card-image {
        width: 100%;
        height: 220px;
        overflow: hidden;
        position: relative;
    }

    .project-card-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .project-card:hover .project-card-image img {
        transform: scale(1.05);
    }

    /* Badge hidden - removed */

    /* Card Content */
    .project-card-content {
        padding: 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    .project-card-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: #1a3a5c;
        margin-bottom: 12px;
        line-height: 1.5;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 3.6em;
        /* Fixed height for 2 lines */
    }

    .project-card-desc {
        font-size: 0.95rem;
        color: #666;
        line-height: 1.6;
        margin-bottom: 20px;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
        min-height: 4.8em;
        /* Fixed height for 3 lines */
    }

    .project-card-footer {
        padding-top: 16px;
        border-top: 1px solid #f0f0f0;
        margin-top: auto;
        /* Push footer to bottom */
    }

    .btn-donate {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #FB974E 0%, #e87d2e 100%);
        color: #fff;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        width: 100%;
        justify-content: center;
    }

    .btn-donate:hover {
        background: linear-gradient(135deg, #e87d2e 0%, #d06820 100%);
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(251, 151, 78, 0.4);
    }

    .btn-donate i {
        transition: transform 0.3s ease;
    }

    .btn-donate:hover i {
        transform: translateX(3px);
    }

    /* Loading & Empty States */
    .projects-loading,
    .projects-empty {
        text-align: center;
        padding: 60px 20px;
    }

    .projects-loading .spinner-border {
        width: 3rem;
        height: 3rem;
    }

    .projects-empty {
        color: #6c757d;
    }
</style>

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
                const response = await fetch(`${API_BASE}/projects`);
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
