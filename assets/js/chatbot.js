document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('chatbot-container');
    const chatWindow = document.getElementById('chat-window');
    const messagesDiv = document.getElementById('chat-messages');
    const input = document.getElementById('chat-input');

    const url = container.dataset.url;

    function toggleChat() {
        chatWindow.classList.toggle('d-none');
    }

    document.getElementById('toggle-btn').addEventListener('click', toggleChat);
    document.getElementById('close-btn').addEventListener('click', toggleChat);

    function addMessage(text, type) {
        const div = document.createElement('div');

        div.classList.add('p-2', 'rounded', 'shadow-sm');
        div.style.maxWidth = '85%';

        if (type === 'user') {
            div.classList.add('bg-primary', 'text-white', 'align-self-end');
            div.textContent = text;
        } else {
            div.classList.add('bg-white', 'border', 'align-self-start');
            div.innerHTML = text;
        }

        messagesDiv.appendChild(div);
        messagesDiv.scrollTop = messagesDiv.scrollHeight;
    }

    async function sendMessage() {
        const text = input.value.trim();
        if (!text) return;

        addMessage(text, 'user');
        input.value = '';
        input.disabled = true;

        try {
            const formData = new FormData();
            formData.append('message', text);

            const response = await fetch(url, {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                const data = await response.json();
                addMessage(data.reply, 'bot');
            } else {
                addMessage("Erreur réseau", 'bot');
            }

            input.disabled = false;

        } catch (error) {
            console.error(error);
            addMessage("Erreur de connexion.", 'bot');
        }
    }

    document.getElementById('send-btn').addEventListener('click', sendMessage);
    input.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') sendMessage();
    });
});
