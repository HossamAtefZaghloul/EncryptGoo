async function chunkUpload(file, uploadPath, progressCallback) {
    try {
        if (!file) {
            console.error("No file provided for upload.");
            return;
        }

        const CHUNK_SIZE = 5 * 1024 * 1024; // 5 MB per chunk
        const totalChunks = Math.ceil(file.size / CHUNK_SIZE);
        let uploadedChunks = 0;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (!csrfToken) {
            throw new Error("CSRF token is missing.");
        }

        for (let i = 0; i < totalChunks; i++) {
            const start = i * CHUNK_SIZE;
            const end = Math.min(file.size, start + CHUNK_SIZE);
            const chunk = file.slice(start, end);

            const formData = new FormData();
            formData.append('chunk', chunk);
            formData.append('chunkIndex', i);
            formData.append('totalChunks', totalChunks);
            formData.append('fileName', file.name);
            // for (let [key, value] of formData.entries()) {
            //     console.log(key, value);
            // }
            try {
                const response = await axios.post(uploadPath, formData, {
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'multipart/form-data',
                    },
                });
                uploadedChunks++;

                if (progressCallback) {
                    const progress = ((uploadedChunks / totalChunks) * 100).toFixed(2);
                    progressCallback(progress, uploadedChunks, totalChunks);
                }

                if (uploadedChunks === totalChunks && response?.data?.download_url) {
                    return response.data.download_url;
                }
            } catch (chunkError) {
                console.error(`Error uploading chunk ${i}:`, chunkError);
                throw new Error(`Failed to upload chunk ${i}.`);
            }
        }
    } catch (error) {
        console.error("File upload failed:", error);
        if (progressCallback) progressCallback(null, error.message);
        return { status: "error", message: error.message };
    }
}




// FileSize Function! :-



