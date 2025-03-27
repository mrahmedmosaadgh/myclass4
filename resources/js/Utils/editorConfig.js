import { Boot } from '@wangeditor/editor'
import formulaModule from '@wangeditor/plugin-formula'

// Register modules only once
let isModuleRegistered = false;

export const initEditor = () => {
    if (!isModuleRegistered) {
        Boot.registerModule(formulaModule);
        isModuleRegistered = true;
    }
};

// Common editor configuration
export const getEditorConfig = () => ({
    placeholder: 'Please enter content...',
    minHeight: 300,
    maxHeight: 500,
    MENU_CONF: {
        uploadImage: {
            maxFileSize: 2 * 1024 * 1024,
            maxNumberOfFiles: 10,
            allowedFileTypes: ['image/*'],
        }
    }
});

// Common toolbar configuration
export const toolbarConfig = {
    excludeKeys: [
        'insertTable',
        'group-video',
        'uploadVideo',
    ]
};


