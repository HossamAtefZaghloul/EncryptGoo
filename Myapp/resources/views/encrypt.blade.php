<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between space-x-4">
            <h2 class="font-semibold text-xl text-primary leading-tight">
                {{ __('Encrypt Page') }}
            </h2>
            <a href="/upload/decrypt" class="text-blue-500">
                Go to Decrypt Page
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white shadow-sm rounded-lg p-6">
                <div class="space-y-6">
                    <div id="success-message" class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 hidden">
                        <p>File Encrypted successfully</p>
                        <p>
                            <a id="download-link" href="#" class="text-green-600 underline">
                                Download Encrypted File
                            </a>
                        </p>
                    </div>

                    <div id="progress-container" class="hidden">
                        <div class="bg-gray-200 rounded-full h-4">
                            <div id="progress-bar" class="bg-green-600 h-4 rounded-full" style="width: 0%;"></div>
                        </div>
                        <p id="progress-text" class="text-sm text-gray-800 mt-2">Encrypting: 0%</p>
                    </div>

                    <form id="chunk-upload-form" class="space-y-4">
                        @csrf
                        <div class="mb-4">
                            <label for="file" class="block text-sm font-medium text-gray-700">Choose file to upload:</label>
                            <input type="file" name="file" id="file" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                onchange="handleFileUpload(event)">
                            <p id="file-size" class="mt-2 text-sm text-gray-800"></p>
                            <p id="file-error" class="mt-2 text-sm text-red-800"></p>
                        </div>
                        <button type="button" id="upload-button"
                            class="w-full py-3 px-4 bg-blue-500 text-white font-semibold rounded-lg hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50"
                            onclick="uploadFileInChunks()">
                            Encrypt and Upload
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        let file = null;

        function handleFileUpload(event) {
            file = event.target.files[0];
            resetUI();
            displayFileSize(event);
            document.getElementById('progress-container').classList.remove('hidden');
        }

        function resetUI() {
            document.getElementById('success-message').classList.add('hidden');
            const downloadLink = document.getElementById('download-link');
            downloadLink.href = "#";
            downloadLink.textContent = "Download Encrypted File";
            document.getElementById('file-error').textContent = "";
            document.getElementById('progress-container').classList.add('hidden');
            document.getElementById('progress-bar').style.width = "0%";
        }

        async function uploadFileInChunks() {
            if (!file) {
                document.getElementById('file-error').textContent = "Please select a file to upload.";
                return;
            }

            const uploadButton = document.getElementById('upload-button');
            uploadButton.disabled = true;
            uploadButton.textContent = "Encrypting...";

            const progressContainer = document.getElementById('progress-container');
            const progressBar = document.getElementById('progress-bar');
            const progressText = document.getElementById('progress-text');
            progressContainer.classList.remove('hidden');

            try {
                const uploadPath = "{{ route('upload.chunk') }}";
                const response = await chunkUpload(file, uploadPath, (progress) => {
                    progressBar.style.width = `${progress}%`;
                    progressText.textContent = `Encrypting: ${progress}%`;
                });

                if (response?.status !== "error") {
                    document.getElementById('success-message').classList.remove('hidden');
                    const downloadLink = document.getElementById('download-link');
                    downloadLink.href = response;
                    downloadLink.textContent = "Download Encrypted File";
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                console.error("File upload failed:", error);
                document.getElementById('file-error').textContent =
                    "Upload failed. Please check your connection and try again.";
            } finally {
                uploadButton.disabled = false;
                uploadButton.textContent = "Encrypt and Upload";
            }
        }

        function displayFileSize(event) {
            const file = event.target.files[0];
            const fileSizeElement = document.getElementById('file-size');
            const largeFileMessage = "File size is large.";
            if (file) {
                let fileSize = file.size;
                let unit = "bytes";
                let isLargeFile = false;

                if (fileSize >= 1024) {
                    fileSize /= 1024;
                    unit = "KB";
                }
                if (fileSize >= 1024) {
                    fileSize /= 1024;
                    unit = "MB";
                }
                if (fileSize >= 1024) {
                    fileSize /= 1024;
                    unit = "GB";
                }

                if (file.size > 100 * 1024 * 1024) {
                    isLargeFile = true;
                }

                fileSizeElement.textContent = `File size: ${fileSize.toFixed(3)} ${unit} ${
                    isLargeFile ? largeFileMessage : ""
                }`;
            } else {
                fileSizeElement.textContent = "";
            }
        }
    </script>
</x-app-layout>
