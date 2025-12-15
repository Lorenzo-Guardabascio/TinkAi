const dotenv = require('dotenv');
const { GoogleGenerativeAI } = require('@google/generative-ai');

dotenv.config();

async function listModels() {
    if (!process.env.GEMINI_API_KEY) {
        console.error("GEMINI_API_KEY not found in .env");
        return;
    }

    const genAI = new GoogleGenerativeAI(process.env.GEMINI_API_KEY);
    
    try {
        // For newer versions of the SDK, we might not have a direct listModels method on genAI instance easily accessible 
        // in the same way as the REST API, but let's try a simple generation with a fallback model to see if auth works.
        // Actually, the SDK doesn't expose listModels directly on the main class in all versions.
        // Let's try to just run a simple prompt with 'gemini-1.5-flash' again to isolate the issue, 
        // but let's print the API key (masked) to be sure it's reading the right file.
        
        console.log("Using API Key:", process.env.GEMINI_API_KEY.substring(0, 5) + "...");
        
        const modelName = "gemini-2.5-flash"; 
        console.log(`Attempting to use model: ${modelName}`);
        
        const model = genAI.getGenerativeModel({ model: modelName });
        const result = await model.generateContent("Hello");
        const response = await result.response;
        console.log("Success! Response:", response.text());
        
    } catch (error) {
        console.error("Error testing Gemini:", error);
    }
}

listModels();