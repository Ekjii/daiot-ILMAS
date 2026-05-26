<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ILMAS Control Center</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom scrollbar for terminal feel */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
    <script>
        const sensorData = @json($logs);
    </script>
</head>
<body class="bg-slate-50 font-sans antialiased text-slate-800 selection:bg-blue-200">

    <nav class="bg-slate-900 text-slate-100 px-6 py-3 shadow-sm border-b border-slate-800 flex justify-between items-center">
        <div class="flex items-center space-x-4">
            <h1 class="text-xl font-semibold tracking-wide">ILMAS <span class="font-light text-slate-400">Control Center</span></h1>
            <div class="hidden md:flex items-center space-x-2 px-3 py-1 bg-slate-800 rounded-md border border-slate-700">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-mono tracking-wider text-slate-300" id="syncStatus">LIVE SYNC : ACTIVE</span>
            </div>
        </div>
        <div class="flex items-center space-x-4 text-sm">
            <div class="text-right flex flex-col">
                <span class="text-[10px] uppercase tracking-widest text-slate-400">Regional Weather (BMKG)</span>
                <span class="font-mono text-slate-200">{{ $weatherInfo }}</span>
            </div>
        </div>
    </nav>

    <main class="container mx-auto p-6 max-w-7xl">
        @if($current)
            <div id="statusCard" class="transition-colors duration-300 rounded-sm shadow-sm p-6 mb-6 flex items-center justify-between border-l-4 
                 {{ $current->status_ai == 1 ? 'bg-rose-50 border-rose-600' : 'bg-white border-emerald-500' }}">
                <div>
                    <h2 class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">System Condition</h2>
                    <p id="statusText" class="text-4xl font-bold tracking-tight {{ $current->status_ai == 1 ? 'text-rose-700' : 'text-emerald-700' }}">
                        {{ $current->status_ai == 1 ? 'CRITICAL : EVACUATE' : 'SYSTEM NORMAL' }}
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs font-bold uppercase tracking-widest text-slate-500 mb-1">Last Telemetry Update</p>
                    <p id="lastUpdate" class="font-mono text-lg font-medium text-slate-700">{{ $current->timestamp }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                <div class="bg-white rounded-sm shadow-sm p-5 border border-slate-200">
                    <h3 class="text-xs uppercase tracking-widest text-slate-500 font-semibold mb-3 border-b border-slate-100 pb-2">Soil Moisture</h3>
                    <div class="flex items-baseline space-x-1">
                        <p id="valMoisture" class="text-3xl font-light text-slate-800 font-mono">{{ number_format($current->soil_moisture, 1) }}</p>
                        <span class="text-sm font-medium text-slate-400">%</span>
                    </div>
                </div>
                <div class="bg-white rounded-sm shadow-sm p-5 border border-slate-200">
                    <h3 class="text-xs uppercase tracking-widest text-slate-500 font-semibold mb-3 border-b border-slate-100 pb-2">Accelerometer (m/s²)</h3>
                    <div id="valAccel" class="grid grid-cols-3 gap-2 text-center font-mono">
                        <div><span class="block text-[10px] text-slate-400">X</span><span class="text-lg">{{ number_format($current->accel_x, 2) }}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Y</span><span class="text-lg">{{ number_format($current->accel_y, 2) }}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Z</span><span class="text-lg">{{ number_format($current->accel_z, 2) }}</span></div>
                    </div>
                </div>
                <div class="bg-white rounded-sm shadow-sm p-5 border border-slate-200">
                    <h3 class="text-xs uppercase tracking-widest text-slate-500 font-semibold mb-3 border-b border-slate-100 pb-2">Gyroscope (rad/s)</h3>
                    <div id="valGyro" class="grid grid-cols-3 gap-2 text-center font-mono">
                        <div><span class="block text-[10px] text-slate-400">X</span><span class="text-lg">{{ number_format($current->gyro_x, 2) }}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Y</span><span class="text-lg">{{ number_format($current->gyro_y, 2) }}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Z</span><span class="text-lg">{{ number_format($current->gyro_z, 2) }}</span></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-sm shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-semibold">Moisture Trend</h3>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded">20 Ticks</span>
                    </div>
                    <canvas id="moistureChart" height="200"></canvas>
                </div>
                <div class="bg-white rounded-sm shadow-sm p-5 border border-slate-200">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-xs uppercase tracking-widest text-slate-500 font-semibold">Vibration Analysis (Accel)</h3>
                        <span class="text-[10px] bg-slate-100 text-slate-500 px-2 py-1 rounded">20 Ticks</span>
                    </div>
                    <canvas id="accelChart" height="200"></canvas>
                </div>
            </div>
        @else
            <div class="flex flex-col items-center justify-center h-64 bg-white border border-slate-200 rounded-sm">
                <svg class="w-12 h-12 text-slate-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                <h2 class="text-lg font-medium text-slate-600">Awaiting Sensor Data</h2>
                <p class="text-sm text-slate-400 mt-1">Ensure the hardware unit is broadcasting telemetry.</p>
            </div>
        @endif
    </main>

    <div class="fixed bottom-6 right-6 z-50 flex flex-col items-end">
        <div id="chatBox" class="hidden bg-white w-80 sm:w-[380px] shadow-2xl border border-slate-300 flex flex-col mb-4 transition-all duration-200 origin-bottom-right rounded-sm">
            <div class="bg-slate-900 text-slate-200 p-3 flex justify-between items-center cursor-default">
                <div class="flex items-center space-x-2">
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M4 15a2 2 0 002 2h12a2 2 0 002-2V9a2 2 0 00-2-2H6a2 2 0 00-2 2v6z"></path></svg>
                    <span class="text-xs font-mono uppercase tracking-widest">Diagnostic Console</span>
                </div>
                <button onclick="toggleChat()" class="text-slate-400 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
            
            <div id="chatHistory" class="p-4 h-72 overflow-y-auto bg-slate-50 flex flex-col space-y-4 font-sans text-sm">
                <div class="text-slate-700 self-start border-l-2 border-slate-400 pl-3">
                    <span class="block text-[10px] uppercase text-slate-400 font-mono mb-1">System</span>
                    Terminal ready. Query parameters or mitigation protocols.
                </div>
            </div>
            
            <div class="p-3 bg-white border-t border-slate-200 flex items-center space-x-2">
                <span class="text-slate-400 font-mono">></span>
                <input type="text" id="chatInput" placeholder="Enter query..." autocomplete="off" class="flex-1 border-none px-2 py-1 focus:outline-none text-sm bg-transparent rounded-none transition-colors font-mono">
                <button onclick="sendMessage()" class="text-slate-500 hover:text-slate-900 px-2 py-1 font-bold text-xs uppercase tracking-wider transition-colors">Exec</button>
            </div>
        </div>

        <button id="chatBtn" onclick="toggleChat()" class="bg-slate-800 hover:bg-slate-900 text-white p-4 shadow-lg flex items-center justify-center transition-transform hover:scale-105 active:scale-95 rounded-sm">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </button>
    </div>

    @if($current)
    <script>
        // --- Chart Configuration (Clean Style) ---
        Chart.defaults.font.family = 'ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
        Chart.defaults.color = '#64748b';
        Chart.defaults.scale.grid.color = '#f1f5f9';

        const labelsTime = sensorData.map(log => log.timestamp.split(' ')[1] || log.timestamp);
        
        const moistureChart = new Chart(document.getElementById('moistureChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labelsTime,
                datasets: [{ 
                    label: 'Moisture', 
                    borderColor: '#0f172a', 
                    backgroundColor: 'rgba(15, 23, 42, 0.05)', 
                    data: sensorData.map(log => log.soil_moisture), 
                    fill: true, tension: 0.4, pointRadius: 0, borderWidth: 2 
                }]
            },
            options: { 
                responsive: true, 
                animation: { duration: 400, easing: 'linear' }, 
                plugins: { legend: { display: false } }, 
                scales: { y: { min: 0, max: 100 } } 
            }
        });

        const accelChart = new Chart(document.getElementById('accelChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: labelsTime,
                datasets: [
                    { label: 'X', borderColor: '#334155', data: sensorData.map(log => log.accel_x), tension: 0.4, pointRadius: 0, borderWidth: 1.5 },
                    { label: 'Y', borderColor: '#94a3b8', data: sensorData.map(log => log.accel_y), tension: 0.4, pointRadius: 0, borderWidth: 1.5 },
                    { label: 'Z', borderColor: '#cbd5e1', data: sensorData.map(log => log.accel_z), tension: 0.4, pointRadius: 0, borderWidth: 1.5 }
                ]
            },
            options: { 
                responsive: true, 
                animation: { duration: 400, easing: 'linear' }, 
                plugins: { legend: { position: 'top', labels: { boxWidth: 12, usePointStyle: true } } } 
            }
        });

        // --- Polling Engine ---
        let lastFetchedTimestamp = "{{ $current->timestamp }}";

        async function checkNewData() {
            try {
                const response = await fetch('/api/sensor-now');
                const latest = await response.json();
                if (!latest) return;

                if (latest.timestamp !== lastFetchedTimestamp) {
                    lastFetchedTimestamp = latest.timestamp;

                    // Update Texts
                    document.getElementById('lastUpdate').innerText = latest.timestamp;
                    document.getElementById('valMoisture').innerText = parseFloat(latest.soil_moisture).toFixed(1);
                    
                    document.getElementById('valAccel').innerHTML = `
                        <div><span class="block text-[10px] text-slate-400">X</span><span class="text-lg">${parseFloat(latest.accel_x).toFixed(2)}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Y</span><span class="text-lg">${parseFloat(latest.accel_y).toFixed(2)}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Z</span><span class="text-lg">${parseFloat(latest.accel_z).toFixed(2)}</span></div>
                    `;
                    document.getElementById('valGyro').innerHTML = `
                        <div><span class="block text-[10px] text-slate-400">X</span><span class="text-lg">${parseFloat(latest.gyro_x).toFixed(2)}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Y</span><span class="text-lg">${parseFloat(latest.gyro_y).toFixed(2)}</span></div>
                        <div><span class="block text-[10px] text-slate-400">Z</span><span class="text-lg">${parseFloat(latest.gyro_z).toFixed(2)}</span></div>
                    `;

                    // Update Status Visuals
                    const statusCard = document.getElementById('statusCard');
                    const statusText = document.getElementById('statusText');
                    
                    if (latest.status_ai == 1) {
                        statusCard.className = "transition-colors duration-300 rounded-sm shadow-sm p-6 mb-6 flex items-center justify-between border-l-4 bg-rose-50 border-rose-600";
                        statusText.className = "text-4xl font-bold tracking-tight text-rose-700";
                        statusText.innerText = "CRITICAL : EVACUATE";
                    } else {
                        statusCard.className = "transition-colors duration-300 rounded-sm shadow-sm p-6 mb-6 flex items-center justify-between border-l-4 bg-white border-emerald-500";
                        statusText.className = "text-4xl font-bold tracking-tight text-emerald-700";
                        statusText.innerText = "SYSTEM NORMAL";
                    }

                    // Update Charts
                    const newTimeLabel = latest.timestamp.includes(' ') ? latest.timestamp.split(' ')[1] : latest.timestamp;
                    
                    moistureChart.data.labels.push(newTimeLabel);
                    moistureChart.data.datasets[0].data.push(latest.soil_moisture);
                    if (moistureChart.data.labels.length > 20) {
                        moistureChart.data.labels.shift();
                        moistureChart.data.datasets[0].data.shift();
                    }
                    moistureChart.update();

                    accelChart.data.labels.push(newTimeLabel);
                    accelChart.data.datasets[0].data.push(latest.accel_x);
                    accelChart.data.datasets[1].data.push(latest.accel_y);
                    accelChart.data.datasets[2].data.push(latest.accel_z);
                    if (accelChart.data.labels.length > 20) {
                        accelChart.data.labels.shift();
                        accelChart.data.datasets[0].data.shift();
                        accelChart.data.datasets[1].data.shift();
                        accelChart.data.datasets[2].data.shift();
                    }
                    accelChart.update();
                }
            } catch (error) {
                console.error("Telemetry sync failed", error);
            }
        }
        setInterval(checkNewData, 500);

        // --- Console / Chat Logic ---
        function toggleChat() {
            const box = document.getElementById('chatBox');
            box.classList.toggle('hidden');
            if(!box.classList.contains('hidden')) {
                document.getElementById('chatInput').focus();
            }
        }

        async function sendMessage() {
            const input = document.getElementById('chatInput');
            const msg = input.value.trim();
            if (!msg) return;

            const chatHistory = document.getElementById('chatHistory');
            
            // User Message (Operator)
            chatHistory.innerHTML += `
                <div class="text-slate-800 self-end text-right border-r-2 border-slate-800 pr-3">
                    <span class="block text-[10px] uppercase text-slate-400 font-mono mb-1">Operator</span>
                    ${msg}
                </div>`;
            input.value = '';
            chatHistory.scrollTop = chatHistory.scrollHeight;

            // Processing Indicator
            const typingId = 'typing-' + Date.now();
            chatHistory.innerHTML += `<div id="${typingId}" class="text-slate-400 text-xs font-mono self-start mt-1 pl-3">> processing...</div>`;
            chatHistory.scrollTop = chatHistory.scrollHeight;

            try {
                const response = await fetch('/api/chatbot', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ message: msg })
                });
                const data = await response.json();

                document.getElementById(typingId).remove();
                
                // System Response
                chatHistory.innerHTML += `
                    <div class="text-slate-700 self-start border-l-2 border-slate-400 pl-3">
                        <span class="block text-[10px] uppercase text-slate-400 font-mono mb-1">System</span>
                        ${data.reply}
                    </div>`;
                chatHistory.scrollTop = chatHistory.scrollHeight;
                
            } catch (error) {
                document.getElementById(typingId).remove();
                chatHistory.innerHTML += `<div class="text-rose-600 font-mono text-xs pl-3">> err: connection refused</div>`;
            }
        }

        document.getElementById('chatInput').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') sendMessage();
        });
    </script>
    @endif
</body>
</html>