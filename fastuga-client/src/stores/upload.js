import { defineStore } from 'pinia'

export const useUploadStore = defineStore('upload', () => {
    

    //var previewImage = null

    const handleUpload = (e) => {
        const image = e.target.files[0];
        const reader = new FileReader();
        reader.readAsDataURL(image);
        reader.onload = e =>{
            previewImage = e.target.result;
            console.log(previewImage);
        }
}
return {handleUpload}

})