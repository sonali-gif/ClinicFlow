<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hospital Management System - ClinicFlow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <style>
        :root {
            --primary-blue: #0d6efd;
            --sidebar-bg: #1e293b;
            --sidebar-active: #0d6efd;
            --bg-body: #f8fafc;
            --card-shadow: 0 1px 3px rgba(0,0,0,0.1);
            --sidebar-width: 250px;
        }

        body { 
            background-color: var(--bg-body); 
            font-family: 'Inter', sans-serif;
            color: #334155;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        .sidebar { 
            min-height: 100vh; 
            width: var(--sidebar-width);
            background: var(--sidebar-bg); 
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1000;
            transition: all 0.3s ease;
            color: #94a3b8;
        }

        .sidebar-brand {
            padding: 20px 24px;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .sidebar-brand i {
            font-size: 1.5rem;
            color: var(--primary-blue);
        }

        .sidebar-brand h4 {
            margin: 0;
            font-weight: 700;
            font-size: 1.25rem;
            letter-spacing: -0.5px;
        }

        .nav-section-label {
            padding: 24px 24px 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #64748b;
        }

        .sidebar .nav-link { 
            color: #94a3b8; 
            padding: 12px 24px; 
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            transition: all 0.2s ease;
            border-left: 4px solid transparent;
        }

        .sidebar .nav-link:hover { 
            color: white;
            background: rgba(255,255,255,0.05);
        }

        .sidebar .nav-link.active { 
            background: var(--sidebar-active); 
            color: white; 
            border-left-color: white;
        }

        .sidebar .nav-link i {
            width: 20px;
            font-size: 1.1rem;
        }

        /* Top Navbar Styling */
        .top-navbar {
            height: 70px;
            background: white;
            border-bottom: 1px solid #e2e8f0;
            position: fixed;
            top: 0;
            right: 0;
            left: var(--sidebar-width);
            z-index: 999;
            padding: 0 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .main-content { 
            margin-left: var(--sidebar-width);
            margin-top: 70px;
            padding: 32px; 
            min-height: calc(100vh - 70px);
        }

        /* Card Styling */
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: var(--card-shadow);
            background: #ffffff;
            margin-bottom: 24px;
        }

        .stat-card {
            padding: 24px;
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .stat-icon {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .icon-blue { background: #eff6ff; color: #3b82f6; }
        .icon-green { background: #f0fdf4; color: #22c55e; }
        .icon-purple { background: #faf5ff; color: #a855f7; }
        .icon-red { background: #fef2f2; color: #ef4444; }

        .stat-info h3 {
            margin: 0;
            font-weight: 700;
            font-size: 1.5rem;
            color: #1e293b;
        }

        .stat-info p {
            margin: 0;
            color: #64748b;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Table Styling */
        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            color: #64748b;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            padding: 16px 24px;
        }

        .table tbody td {
            padding: 16px 24px;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 0.875rem;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.75rem;
        }

        .badge-confirmed { background: #dcfce7; color: #15803d; }
        .badge-pending { background: #fef9c3; color: #854d0e; }
        .badge-cancelled { background: #fee2e2; color: #b91c1c; }

        /* Custom UI Elements */
        .btn {
            border-radius: 8px;
            font-weight: 600;
            padding: 10px 20px;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .btn-primary {
            background: var(--primary-blue);
            border: none;
            box-shadow: 0 4px 6px -1px rgba(13, 110, 253, 0.2);
        }

        .btn-primary:hover {
            background: #025ce2;
            transform: translateY(-1px);
            box-shadow: 0 10px 15px -3px rgba(13, 110, 253, 0.3);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
        }

        .user-avatar {
            width: 38px;
            height: 38px;
            border-radius: 10px;
            background: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #64748b;
        }

        @media (max-width: 992px) {
            .sidebar { left: -100%; }
            .top-navbar { left: 0; }
            .main-content { margin-left: 0; }
            .sidebar.active { left: 0; }
        }
    </style>
</head>
<body>
<div class="container-fluid p-0">
    <div class="d-flex">
        <!-- Sidebar logic will be updated in specific dashboards -->
