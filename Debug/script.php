<?php

return <<<'JS'
<script>
function switchDebugTab(tabName) {
    var bar = document.getElementById('pet-debug-bar');
    bar.classList.remove('collapsed');
    var tabs = bar.querySelectorAll('.pet-debug-tab');
    tabs.forEach(function(t) { t.classList.remove('active'); });
    var contents = bar.querySelectorAll('.pet-debug-content');
    contents.forEach(function(c) { c.classList.remove('active'); });
    var activeTab = bar.querySelector('.pet-debug-tab[data-tab="' + tabName + '"]');
    if (activeTab) activeTab.classList.add('active');
    var activeContent = document.getElementById('pet-debug-tab-' + tabName);
    if (activeContent) activeContent.classList.add('active');
}
function toggleDebugBar() {
    var bar = document.getElementById('pet-debug-bar');
    bar.classList.toggle('collapsed');
}
function toggleVendorFiles() {
    var vendorTable = document.getElementById('pet-debug-vendor-files');
    var checkbox = document.getElementById('pet-debug-show-vendor');
    if (vendorTable && checkbox) {
        vendorTable.style.display = checkbox.checked ? 'table' : 'none';
    }
}
</script>
JS;