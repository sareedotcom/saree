define([
    'jquery'
], function ($) {
    var translation = {
        getTranslation: function(key){
            return (typeof translation.translations[key] === 'undefined' ? key : translation.translations[key]);
        },
        
        setTranslation: function(key, value){
            translation.translations[key] = value;
        },       
        
        init: function(translations){
            $.each(translations, function(index, item) {
                translation.setTranslation(item.key, item.value);                
            });
        },        
        
        translations: []
    };
    
    return translation;
});