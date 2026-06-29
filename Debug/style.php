<style>
.pet-debug-bar * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}
.pet-debug-bar {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-size: 13px;
    line-height: 1.4;
    color: #e0e0e0;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 999999;
    background: #1e1e2e;
    border-top: 2px solid #89b4fa;
    box-shadow: 0 -4px 12px rgba(0,0,0,0.3);
    max-height: 40vh;
    display: flex;
    flex-direction: column;
}
.pet-debug-toolbar {
    display: flex;
    align-items: center;
    gap: 0;
    background: #181825;
    border-bottom: 1px solid #313244;
    flex-shrink: 0;
}
.pet-debug-toolbar .pet-debug-brand {
    padding: 8px 16px;
    font-weight: 700;
    color: #89b4fa;
    font-size: 14px;
    border-right: 1px solid #313244;
    white-space: nowrap;
}
.pet-debug-tab {
    padding: 8px 18px;
    cursor: pointer;
    border: none;
    background: transparent;
    color: #a6adc8;
    font-size: 13px;
    font-family: inherit;
    transition: all 0.15s ease;
    border-bottom: 2px solid transparent;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
}
.pet-debug-tab:hover {
    background: #313244;
    color: #cdd6f4;
}
.pet-debug-tab.active {
    color: #89b4fa;
    border-bottom-color: #89b4fa;
    background: rgba(137, 180, 250, 0.08);
}
.pet-debug-tab .badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 18px;
    height: 18px;
    padding: 0 5px;
    border-radius: 9px;
    font-size: 11px;
    font-weight: 600;
    line-height: 1;
}
.pet-debug-tab .badge.sql-badge {
    background: #a6e3a1;
    color: #1e1e2e;
}
.pet-debug-tab .badge.file-badge {
    background: #f9e2af;
    color: #1e1e2e;
}
.pet-debug-content {
    overflow-y: auto;
    padding: 12px 16px;
    flex: 1;
    min-height: 0;
    display: none;
}
.pet-debug-content.active {
    display: block;
}
.pet-debug-content table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.pet-debug-content th {
    text-align: left;
    padding: 6px 10px;
    background: #313244;
    color: #cdd6f4;
    font-weight: 600;
    border-bottom: 1px solid #45475a;
    position: sticky;
    top: 0;
}
.pet-debug-content td {
    padding: 5px 10px;
    border-bottom: 1px solid #313244;
    color: #bac2de;
    word-break: break-all;
}
.pet-debug-content tr:hover td {
    background: rgba(137, 180, 250, 0.05);
}
.pet-debug-content .query-sql {
    font-family: 'JetBrains Mono', 'Fira Code', 'Consolas', monospace;
    font-size: 12px;
    color: #a6e3a1;
}
.pet-debug-content .query-time {
    font-family: monospace;
    color: #fab387;
    white-space: nowrap;
}
.pet-debug-content .file-path {
    font-family: monospace;
    font-size: 11px;
    color: #bac2de;
}
.pet-debug-content .stat-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    padding: 8px 0;
}
.pet-debug-content .stat-card {
    background: #313244;
    border-radius: 8px;
    padding: 14px 18px;
}
.pet-debug-content .stat-card .stat-label {
    font-size: 11px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    color: #6c7086;
    margin-bottom: 4px;
}
.pet-debug-content .stat-card .stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #cdd6f4;
}
.pet-debug-content .stat-card .stat-value.time {
    color: #89b4fa;
}
.pet-debug-content .stat-card .stat-value.memory {
    color: #a6e3a1;
}
.pet-debug-content .stat-card .stat-value.queries {
    color: #f9e2af;
}
.pet-debug-content .stat-card .stat-value.files {
    color: #fab387;
}
.pet-debug-toggle {
    padding: 8px 16px;
    cursor: pointer;
    border: none;
    background: transparent;
    color: #6c7086;
    font-size: 16px;
    margin-left: auto;
    transition: color 0.15s;
}
.pet-debug-toggle:hover {
    color: #cdd6f4;
}
.pet-debug-bar.collapsed .pet-debug-content,
.pet-debug-bar.collapsed .pet-debug-toolbar .pet-debug-tab {
    display: none;
}
.pet-debug-bar.collapsed .pet-debug-toolbar .pet-debug-brand {
    border-right: none;
}
.pet-debug-bar.collapsed {
    max-height: 40px;
}
</style>