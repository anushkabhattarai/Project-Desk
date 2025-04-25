<?php
// Start session to track if user has seen the welcome page
session_start();

// Set the welcomed flag so user doesn't see welcome page again
$_SESSION['welcomed'] = true;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Welcome to Project Desk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #0B5ED7;
            --primary-hover: #0a51b8;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --bg-light: #f8f9fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            scroll-behavior: smooth;
            background-color: white;
            overflow-x: hidden;
        }

        .section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            padding: 3rem 0;
            position: relative;
        }

        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }

        .btn-get-started {
            font-size: 1.125rem;
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(11, 94, 215, 0.2);
        }

        .btn-get-started:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(11, 94, 215, 0.25);
        }

        h1.display-3 {
            font-weight: 700;
            margin-bottom: 1.5rem;
        }

        .lead {
            font-size: 1.35rem;
            margin-bottom: 2rem;
        }

        .progress-dots {
            position: fixed;
            right: 2rem;
            top: 50%;
            transform: translateY(-50%);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            z-index: 1000;
        }

        .dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #dee2e6;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .dot.active {
            background-color: var(--primary-color);
            transform: scale(1.4);
        }

        .mobile-dots {
            display: none;
            margin: 2rem 0;
            justify-content: center;
            gap: 0.5rem;
        }

        .feature-card {
            padding: 2rem;
            border-radius: 1rem;
            background-color: white;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            margin-bottom: 1.5rem;
            transition: all 0.3s ease;
            border-left: 5px solid var(--primary-color);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background-color: rgba(11, 94, 215, 0.1);
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .video-container {
            position: relative;
            overflow: hidden;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }

        .video-container video {
            width: 100%;
            height: auto;
            display: block;
        }

        .video-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            pointer-events: none;
        }

        .user-role {
            border-radius: 1rem;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 2rem;
            background-color: white;
            padding: 1.5rem;
        }

        .user-role:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1);
        }

        .interactive-demo {
            background-color: white;
            border-radius: 1rem;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            position: relative;
        }

        .interactive-demo-header {
            background-color: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
        }

        .interactive-demo-content {
            padding: 1.5rem;
        }

        .demo-task {
            border-left: 3px solid var(--primary-color);
            background-color: #f8f9fa;
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .demo-task:hover {
            transform: translateX(5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }

        .demo-task.completed {
            border-left-color: #28a745;
            text-decoration: line-through;
            opacity: 0.7;
        }

        .demo-task-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .demo-input {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
            padding: 0.5rem 1rem;
            width: 100%;
            margin-bottom: 1rem;
        }

        @media (max-width: 992px) {
            .progress-dots {
                display: none;
            }

            .mobile-dots {
                display: flex;
            }

            .mobile-dot {
                width: 10px;
                height: 10px;
                border-radius: 50%;
                background-color: #dee2e6;
                transition: all 0.3s ease;
            }

            .mobile-dot.active {
                background-color: var(--primary-color);
                transform: scale(1.3);
            }

            .section {
                min-height: auto;
                padding: 4rem 0;
            }
        }
    </style>
</head>
<body>
    <!-- Progress Dots (Desktop) -->
    <div class="progress-dots">
        <div class="dot active" data-target="section1"></div>
        <div class="dot" data-target="section2"></div>
        <div class="dot" data-target="section3"></div>
    </div>

    <!-- Section 1: Welcome Screen -->
    <section id="section1" class="section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <h1 class="display-3 animate__animated animate__fadeInUp">Project Desk</h1>
                    <p class="lead text-muted animate__animated animate__fadeInUp animate__delay-1s">Your digital workspace to write, manage, and grow.</p>
                    
                    <div class="user-role animate__animated animate__fadeInUp animate__delay-2s">
                        <h5>User Features</h5>
                        <p class="mb-0">Write notes, manage tasks, and upgrade to premium features to enhance your productivity.</p>
                    </div>
                    
                    <a href="#section2" class="btn btn-primary btn-get-started animate__animated animate__fadeInUp animate__delay-3s">
                        Get Started <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
                
                <div class="col-lg-6">
                    <div class="interactive-demo animate__animated animate__fadeIn animate__delay-1s">
                        <div class="interactive-demo-header">
                            <h5 class="mb-0">Try it out</h5>
                        </div>
                        <div class="interactive-demo-content">
                            <h6 class="mb-3">Today's Tasks</h6>
                            
                            <div class="demo-task" onclick="toggleTask(this)">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Complete project proposal</h6>
                                        <small class="text-muted">High priority</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-danger">Due today</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="demo-task" onclick="toggleTask(this)">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Schedule team meeting</h6>
                                        <small class="text-muted">Medium priority</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-warning">Tomorrow</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="demo-task" onclick="toggleTask(this)">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-1">Review client feedback</h6>
                                        <small class="text-muted">Medium priority</small>
                                    </div>
                                    <div>
                                        <span class="badge bg-primary">This week</span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-4">
                                <h6 class="mb-2">Add a new task</h6>
                                <input type="text" class="demo-input" placeholder="What needs to be done?">
                                <button class="btn btn-sm btn-primary" onclick="addDemoTask()">Add Task</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Progress Dots -->
            <div class="mobile-dots">
                <div class="mobile-dot active"></div>
                <div class="mobile-dot"></div>
                <div class="mobile-dot"></div>
            </div>
        </div>
    </section>

    <!-- Section 2: Video Tour -->
    <section id="section2" class="section bg-light">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8 text-center mb-5">
                    <h2 class="display-5 fw-bold mb-4">See how it works</h2>
                    <p class="lead text-muted">Watch this short video to understand how Project Desk can transform your workflow.</p>
                </div>
                
                <div class="col-lg-10">
                    <div class="video-container">
                        <video controls>
                            <source src="intro.mp4" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                </div>
            </div>
            
            <!-- Mobile Progress Dots -->
            <div class="mobile-dots mt-5">
                <div class="mobile-dot"></div>
                <div class="mobile-dot active"></div>
                <div class="mobile-dot"></div>
            </div>
        </div>
    </section>

    <!-- Section 3: Features -->
    <section id="section3" class="section">
        <div class="container">
            <div class="row justify-content-center mb-5">
                <div class="col-lg-8 text-center">
                    <h2 class="display-5 fw-bold mb-4">Key features to boost your productivity</h2>
                    <p class="lead text-muted">Project Desk comes with everything you need to organize your work life.</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-kanban"></i>
                        </div>
                        <h4>Task Management</h4>
                        <p class="text-muted">Create, organize and track your tasks with powerful tools that help you stay on top of your work.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-journal-text"></i>
                        </div>
                        <h4>Note Taking</h4>
                        <p class="text-muted">Capture your ideas and important information in a structured, searchable format.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-people"></i>
                        </div>
                        <h4>Team Collaboration</h4>
                        <p class="text-muted">Share notes, assign tasks, and work together seamlessly with your team.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-bell"></i>
                        </div>
                        <h4>Notifications</h4>
                        <p class="text-muted">Stay informed with timely updates about your tasks and team activities.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-graph-up"></i>
                        </div>
                        <h4>Progress Tracking</h4>
                        <p class="text-muted">Monitor your productivity and track project progress with visual dashboards.</p>
                    </div>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-shield-check"></i>
                        </div>
                        <h4>Data Security</h4>
                        <p class="text-muted">Your information is protected with enterprise-grade security measures.</p>
                    </div>
                </div>
            </div>
            
            <div class="row mt-5">
                <div class="col-12 text-center">
                    <a href="login.php" class="btn btn-primary btn-get-started">
                        Get Started Now <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            
            <!-- Mobile Progress Dots -->
            <div class="mobile-dots mt-4">
                <div class="mobile-dot"></div>
                <div class="mobile-dot"></div>
                <div class="mobile-dot active"></div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function(e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    
                    window.scrollTo({
                        top: target.offsetTop,
                        behavior: 'smooth'
                    });
                    
                    // Update dots
                    updateDots(this.getAttribute('href').substring(1));
                });
            });
            
            // Dot navigation click handlers
            document.querySelectorAll('.dot').forEach(dot => {
                dot.addEventListener('click', function() {
                    const targetId = this.getAttribute('data-target');
                    document.getElementById(targetId).scrollIntoView({ behavior: 'smooth' });
                    
                    // Update dots
                    updateDots(targetId);
                });
            });
            
            // Scroll event to update dots
            window.addEventListener('scroll', function() {
                const scrollPosition = window.scrollY;
                
                document.querySelectorAll('.section').forEach(section => {
                    const sectionTop = section.offsetTop - 200;
                    const sectionBottom = sectionTop + section.offsetHeight;
                    
                    if (scrollPosition >= sectionTop && scrollPosition < sectionBottom) {
                        updateDots(section.id);
                    }
                });
            });
            
            // Function to update dot navigation
            function updateDots(currentSection) {
                // Update desktop dots
                document.querySelectorAll('.dot').forEach(dot => {
                    if (dot.getAttribute('data-target') === currentSection) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
                
                // Update mobile dots
                const sectionIndex = currentSection.charAt(currentSection.length - 1) - 1;
                document.querySelectorAll('.mobile-dot').forEach((dot, index) => {
                    if (index === sectionIndex) {
                        dot.classList.add('active');
                    } else {
                        dot.classList.remove('active');
                    }
                });
            }
        });
        
        // Interactive demo functions
        function toggleTask(element) {
            element.classList.toggle('completed');
        }
        
        function addDemoTask() {
            const input = document.querySelector('.demo-input');
            const taskText = input.value.trim();
            
            if (taskText) {
                const demoTasksContainer = document.querySelector('.interactive-demo-content');
                const newTask = document.createElement('div');
                newTask.className = 'demo-task';
                newTask.setAttribute('onclick', 'toggleTask(this)');
                
                newTask.innerHTML = `
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="mb-1">${taskText}</h6>
                            <small class="text-muted">New task</small>
                        </div>
                        <div>
                            <span class="badge bg-success">Added now</span>
                        </div>
                    </div>
                `;
                
                // Insert after the last task but before the "Add a new task" section
                const addTaskSection = document.querySelector('.mt-4');
                demoTasksContainer.insertBefore(newTask, addTaskSection);
                
                // Clear input
                input.value = '';
            }
        }
    </script>
</body>
</html> 