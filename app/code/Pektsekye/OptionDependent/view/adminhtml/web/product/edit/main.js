
require([
    "jquery",
    "mage/template",     
    "jquery/ui",    
    "Magento_Catalog/js/custom-options"    
],function($, mageTemplate) {

  "use strict";
  $.widget("pektsekye.optionDependent", {
  		
    optionIds : [],	
    rowIds : [],
    lastRowId : 0,	
    lastOptionId : 0,    
    rowIdByOption : [],	
    rowIdsByOption : {},
    optionIdByRowId : [], 
    childrenByRowId : [],
    parentRowIdsOfRowId : [],     
    
    delButtonObserved : [],	
    
    optionRowIdElId : {},    
    childrenFieldId : {},   
    originalIds: [],
 
    observedOptionDelButton : {}, 
              
    options: {						
      idColumn : '',
      childrenColumn : '',	
      importContainer : '',	
      idField : '',
      idFieldTypeSelect : '',	
      childrenField : '',		
      config : [],
    },	
    
    _create: function(){   		    
      this.config = this.options.config;
      
      this.lastRowId = this.options.config[3];	      
      this.lastOptionId = this.options.config[4];  
      this.optionIds = this.options.config[5];       
      this.optionIdByRowId = this.options.config[6];      

      this._on({     
          'blur input.od-children-input': $.proxy(this.childrenFieldUpdated, this),
          'blur select.od-children-select': $.proxy(this.showInput, this),
          'click span.od-show-link': $.proxy(this.showSelect, this),
          'click span.od-hide-link': $.proxy(this.showInput, this)     
      });          
    },
    
    
    
    initRowId: function(el, rowIdStr, isOptionRowId){
      var rowId;

      if (this.deleting == 1)
        return;
      
      el = $(el);

      if (!rowIdStr){//new option. wait until elements initialize 
        setTimeout($.proxy(this.initRowId, this, el, -1, isOptionRowId), 1000);
        return;     
      }
      
      if (rowIdStr == -1) 
        rowIdStr = '';        
        
      if (isOptionRowId && el.closest('fieldset').css('display') != 'block'){ //new option without option type selected
        return;   
      }          

      if (rowIdStr){
        rowId = parseInt(rowIdStr);
        if (rowId > this.lastRowId)
          this.lastRowId = rowId;			
      } else {
        this.lastRowId++;
        rowId = this.lastRowId;
      }	
  
      var children = '';      
      if (this.config[1] && this.config[1][rowId] && this.config[1][rowId][1]){
        children = this.config[1][rowId][1];					
      }
      
      var oId;
      if (this.optionIdByRowId[rowId]){	    
        oId = this.optionIdByRowId[rowId];
      } else {      
        if (!isOptionRowId){
          var rowIdInput = el.closest('div[data-index="values"]').find('input[name$="[row_id]"]').first(); 
          if (rowIdInput.length && rowIdInput.val()){
            var firstRowId = parseInt(rowIdInput.val());
            if (this.optionIdByRowId[firstRowId]){
              oId = this.optionIdByRowId[firstRowId];
            } 
          }
        }
      }	

      if (!oId){
        this.lastOptionId++;
        oId = this.lastOptionId;
      }
      
      if (!this.observedOptionDelButton[oId]){
          var button = el.closest('div[data-role="collapsible-content"]').prev('.fieldset-wrapper-title').find('button.action-delete');

          button.bind('click', $.proxy(this.deleteOption, this, oId));

          var handlers = button.data('events')['click'];
          // take out the handler we just inserted from the end
          var handler = handlers.pop();
          // move it at the beginning
          handlers.splice(0, 0, handler);      
      
        this.observedOptionDelButton[oId] = 1;
      }      
      
      if (this.rowIds.indexOf(rowId) > -1){       
        this.updateRowIdInput(el, rowId, isOptionRowId);                  
        return;
      }      
      
      this.rowIds.push(rowId);

      if (!isOptionRowId){       
        if (!this.rowIdsByOption[oId])
          this.rowIdsByOption[oId] = [];
                
        this.rowIdsByOption[oId].push(rowId);   
        this.childrenFieldId[rowId] = el.closest('div').find('input[id$="_children"]')[0].id;              
      } else {
        this.rowIdByOption[oId] = rowId;
        this.optionRowIdElId[oId] = el.closest('div').find('input[id$="_row_id"]')[0].id;        
      }

      if (this.optionIds.indexOf(oId) == -1){
        this.optionIds.push(oId);      
      }
             
      this.optionIdByRowId[rowId] = oId;

      if (!rowIdStr){
        this.updateRowIdInput(el, rowId, isOptionRowId);
      }  
           
      if (!isOptionRowId){      
        this.setChildrenOfRow(rowId, this.strToArr(children));
      } 
                   
    },
    
    
    updateRowIdInput : function(el, rowId, isOptionRowId){
      if (isOptionRowId){
        el.closest('div').find('input[name$="[row_id]"]').val(rowId).change();
        el.closest('fieldset').find('.od-row-id-label').text(rowId);
      } else {   
        var row = el.closest('tr.data-row');
        row.find('input[name$="[row_id]"]').val(rowId).change();
        row.find('.od-row-id-label').text(rowId);               
      }          
    },
        
        
    reloadSelect : function(input, rowId, children){
      var oId,rowIdEl,rId,oTitleEl,optionTitle,fistRowId,ll,elm,valueTitle;

      var optionId = this.optionIdByRowId[rowId];

      var n = 1;
      var select = '';
      var l = this.optionIds.length;	
      for (var i=0;i<l;i++){
      
        oId = this.optionIds[i];

        if (this.optionIds[i] != optionId){

          var isNotSelectableOption = true;
          if (this.rowIdByOption[oId] && this.rowIdsByOption[oId]){// option type was changed
            if (this.optionRowIdElId[oId]){
              isNotSelectableOption = $('#'+this.optionRowIdElId[oId]).closest('.admin__collapsible-content').find('fieldset[data-index="container_type_static"]').is(':visible');
            }  
          }
          
          if (this.rowIdByOption[oId] && isNotSelectableOption){
            rowIdEl = $('#'+this.optionRowIdElId[oId]);
            oTitleEl = rowIdEl.closest('.admin__collapsible-content').find('fieldset[data-index="container_common"] input[name$="[title]"]');
            
            if (!oTitleEl.is(':visible') || rowIdEl.val() != this.rowIdByOption[oId])
              continue;
              
            optionTitle = oTitleEl.val() ? oTitleEl.val() : this.options.newOptionText;          
          
            rId = this.rowIdByOption[oId];
            select +=	'<option '+(children.indexOf(rId) != -1 ? 'selected' : '')+' value="'+rId+'">'+optionTitle+' '+rId+'</option>';	
            n++;
          } else {
          
            optionTitle = '';       
            if (this.rowIdsByOption[oId].length){
              fistRowId = this.rowIdsByOption[oId][0];
              oTitleEl = $('#'+this.childrenFieldId[fistRowId]).closest('.admin__collapsible-content').find('fieldset[data-index="container_common"] input[name$="[title]"]');
              
              if (!oTitleEl.is(':visible'))
                continue;              
              
              optionTitle = oTitleEl.val() ? oTitleEl.val() : this.options.newOptionText;          
          	}
          	
            select +=	'<optgroup label="'+optionTitle+'">';
            ll = this.rowIdsByOption[oId].length;	
            for (var ii=0;ii<ll;ii++){
              rId = this.rowIdsByOption[oId][ii];
              elm = $('#'+this.childrenFieldId[rId]).closest('tr.data-row').find('input[name$="[title]"]');
              valueTitle = elm.val() ? elm.val() : this.options.newValueText;				

              select +=	'<option '+(children.indexOf(rId) != -1 ? 'selected' : '')+' value="'+rId+'">'+valueTitle+' '+rId+'</option>';
              n++;
            }
            select +=	'</optgroup>';
            n++;
          }
        }
      }	
      if (n > 20)
        n = 20;
      return '<select class="select od-children-select" name="'+input[0].id+'_select" id="'+input[0].id+'_select" multiple size="' + n + '"><option value=""> </option>' + select + '</select>';			
    },	
    
    
    showSelect : function(e){
      if (this.optionIds.length > 1){ 
              
        var rowIdInput = $(e.target).closest('div').find('input[name$="[row_id]"]');
                          
        var rowId = parseInt(rowIdInput.val());
        
        var childrenInput = $(e.target).closest('div').find('input[id$="_children"]');          
        var uid = childrenInput[0].id.replace('_children', '');               
        var children = this.strToArr(childrenInput.val());			              
        var select = this.reloadSelect(childrenInput, rowId, children); 
              
        var selectEl = $(e.target).closest('div').find('select[id$="_children_select"]');
                        	        
        selectEl.replaceWith(select);
        
        childrenInput.hide();
        
        $('#'+uid+'_children_select').show().focus();
        $('#'+uid+'_show_link').hide();
        $('#'+uid+'_hide_link').show();        
      }
    },
    
    
    showInput : function(e){
   
      var select = $(e.target);  
      
      if (select.hasClass('link-type-children'))        
        select = select.closest('div').find('select.od-children-select');   
       
      var uid = select[0].id.replace('_children_select', '');           
      var cIds = select.val() ? this.arrayToInt(select.val()) : [];

      var rowIdInput = $(e.target).closest('tr.data-row').find('input[name$="[row_id]"]');                          
      var rowId = parseInt(rowIdInput.val());
    
   //   var input = '<input class="input-text od-children-select" type="text" name="'+select[0].name+'" id="'+select[0].id+'" data-row-id="'+rowId+'" value="'+cIds.join(',')+'">';
      
  //    select.replaceWith(input);
      
      select.hide();
      
      $('#'+uid+'_children').val(cIds.join(',')).show().focus().change();             
      $('#'+uid+'_hide_link').hide();			
      $('#'+uid+'_show_link').show();
      
      this.setChildrenOfRow(rowId, cIds);
    },		


    childrenFieldUpdated : function(e){
      if (!e.originalEvent)
        return;
      
      var rowIdInput = $(e.target).closest('tr.data-row').find('input[name$="[row_id]"]');                          
      var rowId = parseInt(rowIdInput.val());
      var oId = this.optionIdByRowId[rowId];    
      var childrenInput = $(e.target);      
      var ch = this.strToArr(childrenInput.val());
      var cIds = [];
      var l = ch.length;                    
      for(var i=0;i<l;i++){
        if (this.rowIds.indexOf(ch[i]) != -1 && this.rowIdsByOption[oId].indexOf(ch[i]) == -1 && cIds.indexOf(ch[i]) == -1)
          cIds.push(ch[i]);
      }
      this.setChildrenOfRow(rowId, cIds);      
      this.updateChildrenField(rowId);
    },
    
    
    importDependency : function(productId){
      var widget = this;
      $.ajax({
          type: 'POST',
          url: this.options.importDependencyUrl,
          async: false,
          data: {isAjax:true, form_key: FORM_KEY, product_id: productId},
          dataType: 'json'
      }).success(
          function (data) {
            if (!data.error){
              var l,ll,i,ii,rId,oId,vId,cIds;

              l = data.options.length;
              for(i=0;i<l;i++){
                rId = data.options[i].rowId;
                oId = data.options[i].optionId;
                widget.config[0][oId] = rId + widget.lastRowId;            
              }
              
              l = data.values.length;
              for (i=0;i<l;i++){
                rId = data.values[i].rowId + widget.lastRowId;
                vId = data.values[i].valueId;
                cIds = data.values[i].children;
                
                widget.config[1][vId] = [];
                widget.config[1][vId][0] = rId;
                                              
                ll = cIds.length;               
                for (ii=0;ii<ll;ii++)
                  cIds[ii] += widget.lastRowId;                 
                
                widget.config[1][vId][1] = cIds.join(',');
      
              }   

            }
          }
        );    
    
    },


    setNewOptionId : function(oId, newOId){
      if (this.config[0][oId])
        this.config[0][newOId] = this.config[0][oId];
    },


    deleteOption : function(id){
      this.without(this.optionIds, id);
      
      var rId;
      if (this.rowIdByOption[id]){
        rId = this.rowIdByOption[id];     
        this.without(this.rowIds, rId);
        this.unsetChildren(rId);			
        this.rowIdByOption[id] = null;	
      } else if (this.rowIdsByOption[id]){
        var l = this.rowIdsByOption[id].length;
        while (l--){
          rId = this.rowIdsByOption[id][l];
          this.without(this.rowIds, rId);
          this.unsetChildren(rId);
        }  
        this.rowIdsByOption[id] = null;
      }
      
      this.observedOptionDelButton = {};
    },
    
    
    deleteRow : function(e){
      var rowIdInput = $(e.target).closest('tr.data-row').find('input[name$="[row_id]"]'); 
                   
      var rowId = parseInt(rowIdInput.val());
      var oId = this.optionIdByRowId[rowId]; 
      
      this.without(this.rowIds, rowId);
      this.without(this.rowIdsByOption[oId], rowId);
      this.unsetChildren(rowId);
      
      this.deleting = 1;
      
      this.originalOId = oId;
      this.originalTable = $(e.target).closest('table[data-index="values"]'); 
      this.originalIds = []; 
      
      var removedId = $(e.target).closest('tr.data-row').find('input[id$="_children"]')[0].id;
      var widget = this;      
      $(e.target).closest('table[data-index="values"]').find('input[id$="_children"]').each(function() {
         if (this.id != removedId){
          widget.originalIds.push(this.id);
         }
      });
    },  
    
    
    afterDeleteRow : function(e){
    
      var inputs = this.originalTable.find('input[id$="_children"]');
      
      if (inputs.length == 0 && this.timeoutReloading != 1){//wait until elements initialize 
        setTimeout($.proxy(this.afterDeleteRow, this, e), 1000);
        this.timeoutReloading = 1;
        return;     
      }
      this.timeoutReloading = 0;
      
      this.deleting = 0;
      
      var translateIds = {};

      var widget = this;      
      inputs.each(function(index) {      
         if (this.id != widget.originalIds[index]){
          translateIds[widget.originalIds[index]] = this.id;
         }
      });
      
      var rId,id;
      var l = this.rowIdsByOption[this.originalOId].length;	
      for (var i=0;i<l;i++){
        rId = this.rowIdsByOption[this.originalOId][i];
        id = this.childrenFieldId[rId];
        if (translateIds[id]){
          this.childrenFieldId[rId] = translateIds[id];
          this.updateRowIdInput($('#'+this.childrenFieldId[rId]), rId);
        }
      }
      
    },
    
  
    unsetChildren : function(rowId){
      var rId,vId,oId;

      this.setChildrenOfRow(rowId, []);

      if (this.parentRowIdsOfRowId[rowId] != undefined){

        var l = this.parentRowIdsOfRowId[rowId].length;		    
        while (l--){
          rId = this.parentRowIdsOfRowId[rowId][l];
                 
          this.without(this.childrenByRowId[rId], rowId);           
   
          this.updateChildrenField(rId);
        }      
      
      }	
    },  
  
    
    updateChildrenField : function(rowId){
      var cStr = this.childrenByRowId[rowId].join(',');
      if (this.childrenFieldId[rowId]){           
        $('#'+this.childrenFieldId[rowId]).val(cStr).change();
      }  
    },    
    
    
    setChildrenOfRow : function(rowId, ids){
      var l;
      var previousIds = this.childrenByRowId[rowId] != undefined ? this.childrenByRowId[rowId].slice(0) : []; 
        
      this.childrenByRowId[rowId] = ids;
    
      l = previousIds.length;	     
      while (l--)
        if (ids.indexOf(previousIds[l]) == -1)
          this.without(this.parentRowIdsOfRowId[previousIds[l]], rowId);
            
      l = ids.length;
      while (l--){
        if (this.parentRowIdsOfRowId[ids[l]] == undefined)
          this.parentRowIdsOfRowId[ids[l]] = [];
        this.parentRowIdsOfRowId[ids[l]].push(rowId);
      }  
          
    },
    
    
    strToArr : function(str){
      return this.arrayToInt(str.split(','));         	  
    },
    
    arrayToInt : function (a){
      var t = [];
      var l = a.length;
      for(var i=0;i<l;i++)
        if (a[i] != '')
          t.push(parseInt(a[i]));
      return t;
    },    
    
    without : function(a, v){
      var i = a.indexOf(v);
      if (i != -1)
        a.splice(i, 1);
    }	
        		
  });


});


