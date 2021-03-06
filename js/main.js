;(function($){

    /**
     * 中国驻法国　- 学联健康包核对平台
     *
     * @author Zhao ZHANG <zo.zhang@gmail.com>
     * @github http://github.com/zozhang/paquet-sante
     */
    let sante = {

        initialise: function() {

            this.help = $('#help');
            this.image = $('#image');
            this.uploader1 = $("#fileuploader");
            this.checkListRow = $('#check-list');

            this.fileuploader();
        },

        fileuploader: function() {
            let obj = this;

            obj.sommaire = '<div class="sommaire"><label class="label label-success">已登记</label> <span><b>#IN_LIST#</b></span>&nbsp;&nbsp;&nbsp;&nbsp;<label class="label label-danger">未登记</label> <span><b>#NOT_IN_LIST#</b></span>';
            obj.errorMsg = '<div class="ajax-file-upload-error">#MESSAGE#</div>';
            obj.successMsg = '<div class="ajax-file-upload-success">#MESSAGE#</div>';

            obj.checkList = ' <table class="table table-hover text-center">\n' +
                '                        <thead>\n' +
                '                        <tr>\n' +
                '                        <th scope="col" width="8%">姓名</th>\n' +
                '                        <th scope="col" width="10%">性别</th>\n' +
                '                        <th scope="col" width="10%">登记状态(使馆+学联)</th>\n' +
                '                        <th scope="col" width="10%">电话</th>\n' +
                '                        <th scope="col" width="10%">邮箱</th>\n' +
                '                        <th scope="col" width="10%">学校</th>\n' +
                '                        <th scope="col" width="10%">地址</th>\n' +
                '                        </tr>\n' +
                '                        </thead>\n' +
                '                        <tbody>\n' +
                                         '#CHECK_LIST#' +
                '                    </tbody>\n' +
                '                    </table>';

            obj.uploader1.uploadFile({
                url:"upload.php",
                fileName:"table",
                returnType:"json",
                multiple:true,
                autoSubmit: true,
                showProgress:true,
                allowedTypes:"csv",
                dragDropStr: "<span><b>选中文件拖拽到这里或者点击上传.</b></span>",
                abortStr:"取消",
                cancelStr:"取消",
                doneStr:"完成",
                extErrorStr:"上传文件类型错误，仅支持",
                sizeErrorStr:"上传文件大小超出范围。",
                uploadErrorStr:"上传文件类型错误。",
                uploadStr:"上传",
                onSelect:function(files)
                {
                    obj.help.hide();
                    return true;
                },
                onSuccess:function(files,data,xhr) {

                    if (data.error) {
                        obj.checkListRow.html('<hr/>');
                        $('.ajax-file-upload-success', obj.uploader1).empty().remove();
                        obj.uploader1.append(obj.errorMsg.replace('#MESSAGE#', data.message));
                        return;
                    }

                    // success
                    obj.image.slideUp(300);
                    $('.sommaire').empty().remove();
                    $('.ajax-file-upload-error', obj.uploader1).empty().remove();
                    obj.uploader1.append(obj.successMsg.replace('#MESSAGE#', data.message));

                    let listHtml = '';
                    if (data.resultat) {
                        for(let index in data.resultat) {
                            let personne = data.resultat[index];

                            let html = '<tr><td>#name#</td><td>#sexe#</td><td>#status#</td><td>#tel#</td><td>#mail#</td><td>#univ#</td><td>#addr#</td></tr>';

                            html = html.replace('#name#', personne[0] == "" ? "无名" : personne[0]);
                            html = html.replace('#sexe#', (personne[1] == "" ? "未知" : (personne[1] === 'F' ? '女' : '男')));
                            html = html.replace('#status#', personne.status ? '<label class="label label-success">已登记</label>' : '<label class="label label-danger">未登记</label>');
                            html = html.replace('#tel#', personne[2] == "" ? "未知" : personne[2]);
                            html = html.replace('#mail#', personne[3] == "" ? "未知" : personne[3]);
                            html = html.replace('#univ#', personne[5] == "" ? "未知" : personne[5]);
                            html = html.replace('#addr#', personne[4] == "" ? "未知" : personne[4]);

                            listHtml += html;
                        }

                    obj.uploader1.parent().prepend(obj.sommaire.replace('#IN_LIST#', data.total_counted).replace('#NOT_IN_LIST#', data.not_counted));

                    // check list data
                    obj.checkListRow.html(obj.checkList.replace('#CHECK_LIST#', listHtml));
                    }
                }
            });
        }
    };

    $(document).ready(function(){
        sante.initialise();
    });

})(jQuery);