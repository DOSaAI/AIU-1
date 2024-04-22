document.addEventListener("DOMContentLoaded", async function () {
    // Function to add a message to the chat container
    function addMessage(content, isUser = false, isError = false, callback = null) {
        const chatContainer = document.querySelector(".chat-container");
        const chat = document.createElement("div");
        chat.classList.add("chat", isUser ? "outgoing" : "incoming");

        // Define avatar URLs
        const avatarURL = isUser
            ? "https://raw.githubusercontent.com/DOSaAI/AIU-1_10Million_large_answer/main/User.png"
            : "https://raw.githubusercontent.com/DOSaAI/AIU-1_10Million_large_answer/main/Bot.png"
        // Create and append avatar image
        const avatar = document.createElement("img");
        avatar.src = avatarURL;
        avatar.alt = isUser ? "User Avatar" : "Bot Avatar";
        chat.appendChild(avatar);

        // Create and append message details
        const message = document.createElement("div");
        message.classList.add("chat-details");
        const messageText = document.createElement("p");
        message.appendChild(messageText);
        chat.appendChild(message);

        // Append chat to the chat container
        chatContainer.appendChild(chat);

        // Function to display words with a delay
        async function displayWords(speed) {
            const words = content.split(" ");
            for (const word of words) {
                await new Promise(resolve => setTimeout(resolve, speed));
                messageText.textContent = (messageText.textContent + ' ' + word).trim();
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }

        // Determine display speed based on word count
        const wordsCount = content.split(" ").length;
        const speed = wordsCount > 25 ? 50 : 100;

        // Display words with delay for incoming messages
        if (!isUser) {
            displayWords(speed).then(() => {
                if (callback) {
                    callback();
                }
            });
        } else {
            // Display user's message immediately
            messageText.textContent = content;
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }

        // Apply red color to error messages
        if (isError) {
            messageText.style.color = 'red';
        }
    }

    // Function to fetch the bot's answer from the server
    async function getBotAnswer(userQuestion) {
        try {
            const response = await fetch("server-side.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ question: userQuestion }),
            });

            if (response.ok) {
                const data = await response.json();

                if (data.answer !== undefined) {
                    return {
                        answer: data.answer,
                        isError: false,
                    };
                }
            }
        } catch (error) {
            console.error("Error fetching answers:", error);
        }

        // Return a default response for errors
        return {
            answer: "I'm not sure how to respond to that.",
            isError: true,
        };
    }

    // Function to handle user input
    async function handleUserInput() {
        const inputField = document.querySelector("textarea");
        const userMessage = inputField.value.trim();

        // Return if the input is empty
        if (userMessage === "") {
            return;
        }

        // Clear the input field
        inputField.value = "";

        // Fetch the bot's response
        const botResponse = await getBotAnswer(userMessage);

        // Add user's message to the chat
        addMessage(userMessage, true);

        // Add bot's response to the chat
        addMessage(botResponse.answer, false, botResponse.isError);

        // Scroll to the bottom of the chat container
        const chatContainer = document.querySelector(".chat-container");
        chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    // Event listener for the send button
    const sendButton = document.querySelector(".send-button");
    sendButton.addEventListener("click", handleUserInput);

    // Event listener for the Enter key in the input field
    const inputField = document.querySelector("textarea");
    inputField.addEventListener("keydown", (event) => {
        if (event.key === "Enter") {
            event.preventDefault();
            handleUserInput();
        }
    });
});
