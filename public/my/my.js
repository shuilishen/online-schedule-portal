var mylayerIndex;
var tabCloseEvent=null;



$(document).ready(function () {

    $('.my-toolbar-remove').on('click', function (event) {
        var target = $(this);
        var url = target.data('url');
        var table = target.data('table');

        var selectedRows = $('#'+table).jqGrid('getGridParam', 'selarrrow');
        $.post(url, {key: selectedRows}, function (json) {
            var res = $.parseJSON(json);
            console.log(res);
            $('#'+table).jqGrid().trigger("reloadGrid");
        }, 'json');
    });

    // $('.my-layer').on('click',function () {
    //     console.log(this);
    //
    //     var t=$(this);
    //     var url = t.data('url');
    //     var title=t.data('title');
    //     if(undefined===title || title.length<1){
    //         title=t.text(); //jQuery DOM
    //     }
    //
    //     $.get(url,function (html) {
    //         mylayerIndex = layui.layer.open({
    //             type: 1,
    //             skin: 'layui-layer-lan',
    //             offset: 'auto',
    //             area: ['600px','600px'],
    //             title: title,
    //             content: html
    //         });
    //
    //         if("undefined"!==typeof layerjs){
    //             layerjs();
    //         }
    //         layui.form.render();
    //
    //     });
    // });
});


layui.form.on('submit(layerform)', function(data){
    console.log('submit');
    //console.log(data.form.action);
     //console.log(data.elem) ;//被执行事件的元素DOM对象，一般为button对象
    //console.log(data.form) ;//被执行提交的form对象，一般在存在form标签时才会返回
    layui.$.post(data.form.action, data.field, function (json) {
        console.log(json);
        //console.log(json.code);
        //console.log(json.msg);
        layui.layer.open({
            content:json.msg,
            anim:1,
            time:2000
        });

        if('function'=== typeof tabCloseEvent){
            tabCloseEvent(json);
            tabCloseEvent=null;
        }

        if(json.code === 1){
            layui.layer.close(mylayerIndex);
        }
        else{
//ToDo 字段标红
            var errordom = $("input[name='"+json.errorID+"']");
            console.log(errordom);
            errordom.addClass('layui-form-danger').focus().on('click',function () {
                //$(this).removeClass('layui-form-danger');
            });

            layui.form.render();
        }
    }, 'json');
    return false; //阻止表单跳转。如果需要表单跳转，去掉这段即可。
});

layui.table.on('tool(test)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
    var data = obj.data; //获得当前行数据
    console.log(data.id);
    var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
    var tr = obj.tr; //获得当前行 tr 的DOM对象

    var t = $(this);
    console.log(this);
    var url = t.data('url');
    var title=t.data('title');


    switch(obj.event){
        case 'add':
            $.post(url,'',function (html) {
                mylayerIndex = layui.layer.open({
                    type: 1,
                    skin: 'layui-layer-lan',
                    offset: 'auto',
                    area: ['600px','600px'],
                    title: title,
                    content: html
                });

                if("undefined"!==typeof layerjs){
                    layerjs();
                }
                layui.form.render();
            });
            break;
        case 'del':
            layer.confirm('真的删除行么', function(index){
                console.log(index);

                $.post(url, {key: id}, function (json) {
                    layui.layer.open({
                        content: json.msg,
                        anim: 1,
                        time: 2000
                    });

                    if('function'=== typeof tabCloseEvent){
                        tabCloseEvent(json);
                        tabCloseEvent=null;
                    }
                });
            });
            break;
        case 'edit':
            $.post(url, {key: data.id},function (html) {
                mylayerIndex = layui.layer.open({
                    type: 1,
                    skin: 'layui-layer-lan',
                    offset: 'auto',
                    area: ['600px','600px'],
                    title: title,
                    content: html
                });

                if("undefined"!==typeof layerjs){
                    layerjs();
                }
                layui.form.render();
            });
            break;
    }

});

layui.table.on('toolbar(test)', function(obj){

    var t = $(this);
    console.log(this);
    var url = t.data('url');
    var title=t.data('title');
    if(undefined===title || title.length<1){
        title=t.text(); //jQuery DOM
    }

    var checkStatus = layui.table.checkStatus(obj.config.id);
    var data = checkStatus.data;

    var id = data.map(function (elem) {
        return elem.id;
    });

    switch(obj.event){
        case 'add':
            $.post(url,'',function (html) {
                mylayerIndex = layui.layer.open({
                    type: 1,
                    skin: 'layui-layer-lan',
                    offset: 'auto',
                    area: ['600px','600px'],
                    title: title,
                    content: html
                });

                if("undefined"!==typeof layerjs){
                    layerjs();
                }
                layui.form.render();
            });
            break;
        case 'del':
            layer.confirm('真的删除行么', function(index){
                console.log(index);

                $.post(url, {key: id}, function (json) {
                    layui.layer.open({
                        content: json.msg,
                        anim: 1,
                        time: 2000
                    });

                    if('function'=== typeof tabCloseEvent){
                        tabCloseEvent(json);
                        tabCloseEvent=null;
                    }
                });
            });
            break;
        case 'edit':
            layer.msg('编辑');
            break;
    }
});

