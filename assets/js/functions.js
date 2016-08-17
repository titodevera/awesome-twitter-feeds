jQuery.noConflict();
(function($) {
    $.fn.atfHttpRequest = function($clearPreviousContent,$maxId){
        var mainElementContent = $('.atf-content',this);
        var mainElement = $(this);

        var post_id = mainElement.data('atf-postid');
        var data = {
            'action': 'load_atf',
            'post_id': post_id
        };

        if($maxId!=false){
            data.max_id = $maxId;
        }

        var translations = ajax_object.translations;

        jQuery.post(ajax_object.ajax_url, data, function(response) {

            if($clearPreviousContent){
                mainElementContent.html('');
            }

            var result = '';
            var widgetOptions = '';
            var twitterResponse = '';
            var is_error = false;

            try{
                result = jQuery.parseJSON(response);

                widgetOptions = result['atf_options'];
                twitterResponse = result['twitter_response'];

                /*
                if(result['cache']){
                    console.log("cache!");
                    if(result['is_error']){
                        console.log("is error!");
                        if($maxId!=false){
                            is_error = true;
                        }
                    }else{
                        console.log("no error!");
                    }
                }else{
                    console.log("no cache!");
                }
                */

                if(!is_error){

                    if($maxId!=false){
                        //remove first element (first tweet) when "load more" is clicked, prevent duplicity
                        twitterResponse = twitterResponse.slice(1);
                    }

                    var tweetText = '';
                    var tweetUser = '';
                    var tweetTextRt = '';
                    var tweetUserRt = '';
                    var accountUrlRt = '';
                    var tweetAccount = '';
                    var accountUrl = '';
                    var favsCount = '';
                    var retweetsCount = '';
                    var userPhoto = '';
                    var retweetedClass = '';
                    var followMeHtml = '';
                    var tweetsToAdd = '';

                    $.each(twitterResponse, function(index,tweet){
                        tweetId = tweet.id_str;
                        tweetReplyUrl = "https://twitter.com/intent/tweet?in_reply_to="+tweetId;
                        tweetRetweetUrl = "https://twitter.com/intent/retweet?tweet_id="+tweetId;
                        tweetLikeUrl = "https://twitter.com/intent/favorite?tweet_id="+tweetId;
                        tweetText = tweet.text;
                        accountUrl = 'https://twitter.com/'+tweet.user.screen_name;
                        retweetedClass = '';
                        tweetUser = tweet.user.name;
                        tweetAccount = '@'+tweet.user.screen_name;
                        var retweetHtml = '';

                        if(tweet.retweeted){
                            favsCount = tweet.retweeted_status.favorite_count;
                            retweetsCount = tweet.retweeted_status.retweet_count;
                            userPhoto = tweet.retweeted_status.user.profile_image_url_https;
                            retweetedClass = ' atf-retweeted';

                            tweetUserRt = tweet.retweeted_status.user.name;
                            tweetAccountRt = '@'+tweet.retweeted_status.user.screen_name;
                            accountUrlRt = 'https://twitter.com/'+tweet.retweeted_status.user.screen_name;

                        }else{
                            favsCount = tweet.favorite_count;
                            retweetsCount = tweet.retweet_count;
                            userPhoto = tweet.user.profile_image_url_https;
                        }

                        /* -------------------------- TWEET DATE -------------------------- */
                        var tweetDate = tweet.created_at;
                        tweetDate = new Date(tweetDate.replace("+0000 ", "")/* IE fix for dates */);
                        month = parseInt(tweetDate.getMonth()+1);
                        if(month<10){month='0'+month;}
                        day = tweetDate.getDate();
                        if(day<10){day='0'+day;}
                        dateFormated = day+'/'+month+'/'+tweetDate.getFullYear();
                        /* -------------------------- /TWEET DATE -------------------------- */

                        /* -------------------------- FINDING URLS -------------------------- */
                        youtubeVideoUrl = false;
                        vineVideoUrl = false;

                        $(tweet.entities.urls).each(function(index, element) {
                            if(widgetOptions.links == 'on'){
                                url = '<a href="'+this.url+'" target="_blank" rel="nofollow">'+this.url+'</a>';
                                tweetText = tweetText.replace(this.url,url);
                            }

                            /* YOUTUBE LINKS?? */
                            youtubeVideoUrl = false;
                            var youtubePatt = new RegExp("youtu.be");
                            if(youtubePatt.test(this.expanded_url)){
                                expandedUrl = this.expanded_url;
                                expandedUrlV = expandedUrl.split("youtu.be/");
                                youtubeVideoUrl = 'https://www.youtube.com/watch?v='+expandedUrlV[1];
                                youtubeThumbUrl = 'https://i.ytimg.com/vi/'+expandedUrlV[1]+'/mqdefault.jpg';
                            }

                            /* VINE LINKS?? */
                            vineVideoUrl = false;
                            var vinePatt = new RegExp("vine.co");
                            if(vinePatt.test(this.expanded_url)){
                                vineExpandedUrl = this.expanded_url;
                                vineExpandedUrlV = vineExpandedUrl.split("https://vine.co/v/");
                                vineVideoUrl = 'https://vine.co/v/'+vineExpandedUrlV[1];
                                vineId = vineExpandedUrlV[1];
                            }

                        });
                        if(widgetOptions.links == 'on'){
                            $(tweet.entities.hashtags).each(function(index, element) {
                                hUrl = '<a href="https://twitter.com/hashtag/'+this.text+'" target="_blank" rel="nofollow">#'+this.text+'</a>';
                                tweetText = tweetText.replace('#'+this.text,hUrl);
                            });
                            $(tweet.entities.user_mentions).each(function(index, element) {
                                mUrl = '<a href="https://twitter.com/'+this.screen_name+'" target="_blank" rel="nofollow">@'+this.screen_name+'</a>';

                                /* Making replace case insensitive */
                                var patternSn = new RegExp('@'+this.screen_name, 'gi');
                                tweetText = tweetText.replace(patternSn,mUrl);
                            });

                            if(tweet.retweeted){
                                if(tweet.retweeted_status.extended_entities){
                                    $(tweet.retweeted_status.extended_entities.media).each(function(index, element) {
                                        url = '<a href="'+this.url+'" target="_blank" rel="nofollow">'+this.url+'</a>';
                                        tweetText = tweetText.replace(this.url,url);
                                    });
                                }

                                retweetHtml = '<div class="atf-retweet-notice"><i class="atficon atficon-retweet"></i>'+tweetUser+' retweeted</div>';
                            }else{
                                if(tweet.extended_entities){
                                    $(tweet.extended_entities.media).each(function(index, element) {
                                        /** PARSE MEDIA URL AS LINK **/
                                        url = '<a href="'+this.url+'" target="_blank" rel="nofollow">'+this.url+'</a>';
                                        tweetText = tweetText.replace(this.url,url);
                                    });
                                }
                            }
                        }
                        /* -------------------------- /FINDING URLS -------------------------- */

                        /* -------------------------- ATTACHED MEDIA -------------------------- */
                        var media = "";
                        var showmedia = "";
                        var attachedMedia = tweet.extended_entities;
                        var mediaContent = false;
                        if(widgetOptions.media == 'on'){
                            if($(attachedMedia).length > 0){
                                attachedMedia = tweet.extended_entities.media;
                                attachedMedia.forEach(function(attachedMedia) {
                                    var mediaType = attachedMedia.type;
                                    var mediaUrl = attachedMedia.media_url;
                                    var mediaSizeHeight = attachedMedia.sizes.large.h;
                                    var mediaSizeWidth = attachedMedia.sizes.large.w;
                                    showmedia = '<a href="#" class="showmediatweet" title="Media content"></a>';
                                    if(mediaType == "photo"){
                                        media = '<img src="'+mediaUrl+'" style="max-width:'+mediaSizeWidth+'px;width:100%;">';
                                        mediaContent = true;
                                    }else if(mediaType == "video" || mediaType == "animated_gif"){
                                        //not supported
                                    }else{
                                        //no other types
                                    }

                                });
                            }else if(youtubeVideoUrl != false){
                                showmedia = '<a href="#" class="showmediatweet" title="Media content"></a>';
                                media = '<div class="atf-video-overlay"></div><img src="'+youtubeThumbUrl+'" class="atf-youtube-video" data-videourl="'+expandedUrlV[1]+'" style="width:100%;">';
                                mediaContent = true;
                            }else if(vineVideoUrl != false){
                                showmedia = '<a href="#" class="showmediatweet" title="Media content"></a>';
                                media = '<iframe class="vine-embed" src="https://vine.co/v/'+vineId+'/embed/simple"></iframe><script async src="//platform.vine.co/static/scripts/embed.js" charset="utf-8"></script>';
                                mediaContent = true;
                            }
                        }
                        /* -------------------------- /ATTACHED MEDIA -------------------------- */



                        var tweetIdClass = 'atf-tweet-'+tweetId;
                        if(index==0 && widgetOptions.follow == 'on'){
                            followMeHtml = '<a href="https://twitter.com/intent/user?screen_name='+tweetAccount+'" title="Follow '+tweetUser+'" class="atf-follow"><i class="atficon atficon-bird"></i>Follow <span>'+tweetUser+'</span></a>';
                        }

                        var accountImgHtml = '';
                        var avatarClass = '';
                        if(widgetOptions.profile_img == 'on'){
                            accountImgHtml = '<img src="'+userPhoto+'" width="32" height="32" alt="Twitter avatar">';
                            avatarClass = ' atf-avatar-on';
                        }

                        var accountUrlHtml = '';
                        if(tweet.retweeted){
                            accountUrlHtml = '<a href="'+accountUrlRt+'" title="'+tweetAccountRt+'">'+accountImgHtml+tweetUserRt+'<span>'+tweetAccountRt+'</span></a>';
                        }else{
                            accountUrlHtml = '<a href="'+accountUrl+'" title="'+tweetAccount+'">'+accountImgHtml+tweetUser+'<span>'+tweetAccount+'</span></a>';
                        }

                        var mediaHtml = '<div class="atf-tweet-media">'+media+'</div>';
                        var htmlTweet = '<div class="atf-tweet-content">'+tweetText+'</div>';
                        var mediaContentAction = '';
                        if(mediaContent){
                            mediaContentAction = '<a href="#" title="'+translations.more+'" class="atf-show-media"><i class="atficon atficon-plus"></i></a>';
                        }
                        var tweetActionsHtml = '<div class="atf-tweet-actions"><a href="'+tweetLikeUrl+'" title="'+translations.like+'"><i class="atficon atficon-like" target="_blank"></i></a><a href="'+tweetRetweetUrl+'" title="'+translations.share+'"><i class="atficon atficon-share" target="_blank"></i></a><a href="'+tweetReplyUrl+'" title="'+translations.reply+'" target="_blank"><i class="atficon atficon-reply"></i></a>'+mediaContentAction+'</div>';
                        var tweetMetadataHtml = '<div class="atf-tweet-metadata">'+dateFormated+'</div>';
                        var htmlTweetFooter = '<div class="atf-tweet-footer atf-clear">'+tweetActionsHtml+tweetMetadataHtml+'</div>';
                        var htmlOutput = '<div class="atf-single-tweet '+tweetIdClass+retweetedClass+avatarClass+'" data-atftweetid="'+tweetId+'">'+retweetHtml+'<div class="atf-row-names">'+accountUrlHtml+'</div>'+htmlTweet+mediaHtml+htmlTweetFooter+'</div>';

                        tweetsToAdd += htmlOutput;

                    });

                    mainElementContent.append(tweetsToAdd);

                    if($('.atf-bottom',mainElement).length=='0' && followMeHtml!=''){
                        mainElement.append('<div class="atf-bottom atf-clear">'+followMeHtml+'</div>');
                    }
                    if($maxId!=false){
                        $('.atf-load-more',mainElement).remove();
                        $('.atf-loader-row',mainElement).remove();
                    }

                    mainElementContent.append('<div class="atf-load-more">'+translations.load_more+'</div>');


                    /* -------------------------- BINDING EVENTS TO CREATED ELEMENTS -------------------------- */
                    $('.atf-show-media',mainElement).unbind('click').on('click',function(e){
                        e.preventDefault();
                        var singleTweet = $(this).parents('.atf-single-tweet');
                        var singleTweetMedia = singleTweet.children('.atf-tweet-media');
                        singleTweetMedia.toggle('fast',function(){
                            if($(this).is(':visible')){
                                $(this).siblings('.atf-tweet-footer').find('.atficon-plus').addClass('atficon-minus');
                                $(this).siblings('.atf-tweet-footer').find('.atficon-plus').removeClass('atficon-plus');
                            }else{
                                $(this).siblings('.atf-tweet-footer').find('.atficon-minus').addClass('atficon-plus');
                                $(this).siblings('.atf-tweet-footer').find('.atficon-minus').removeClass('atficon-minus');
                            }
                        });
                    });

                    $('.atf-video-overlay',mainElement).unbind('click').on('click',function(){
                        if($(this).siblings('.atf-youtube-video').length){
                            var youtubeId = $(this).siblings('.atf-youtube-video').attr('data-videourl');
                            $(this).parent('.atf-tweet-media').html('<iframe src="//www.youtube.com/embed/'+youtubeId+'?rel=0&autoplay=1" frameborder="0" allowfullscreen></iframe>');
                        }
                    });


                    $('.atf-load-more',mainElement).unbind('click').on('click',function(){
                        $('.atf-load-more',mainElement).replaceWith('<div class="atf-loader-row"><div class="atf-loader">Loading...</div></div>');
                        var lastTweetId = $('.atf-single-tweet',mainElement).last().data('atftweetid');
                        mainElement.atfHttpRequest(false,lastTweetId);
                    });
                    /* -------------------------- /BINDING EVENTS TO CREATED ELEMENTS -------------------------- */

                    mainElement.atfResponsive();
                }else{
                    $('.atf-loader-row',mainElement).html('<div style="background:red!important;color:white!important;">ERROR: Try later...</div>');

                    setTimeout(function(){
                        $('.atf-loader-row',mainElement).replaceWith('<div class="atf-load-more">Load more</div>');

                        $('.atf-load-more',mainElement).unbind('click').on('click',function(){
                            $('.atf-load-more',mainElement).replaceWith('<div class="atf-loader-row"><div class="atf-loader">Loading...</div></div>');
                            var lastTweetId = $('.atf-single-tweet',mainElement).last().data('atftweetid');
                            mainElement.atfHttpRequest(false,lastTweetId);
                        });
                    },3000);

                }

            }catch(e){
                result = response;
                mainElementContent.html(response);
            }finally{
                //console.log(result);
            }


        });

    }

    $.fn.atfResponsive = function(){
        //adjust widget to viewport
        var mainElement = $(this);
        var atfViewport = $('.atf-content',mainElement).width();
        if(atfViewport<220){
            $('.atf-row-names img',mainElement).hide();
            $('.atf-row-names',mainElement).css('padding-left','0');
            $('.atf-row-names span',mainElement).hide();
            $('.atf-show-media',mainElement).hide();
            $('.atf-tweet-content',mainElement).css('margin-left','0');
            $('.atf-retweet-notice',mainElement).css('margin-left','0');
            $('.atf-tweet-actions',mainElement).css({'float':'none','width':'100%'});
            $('.atf-tweet-metadata',mainElement).css({'float':'none','width':'100%','text-align':'left'});
            $('.atf-bottom',mainElement).css({'padding':'0 0 0 10px','text-align':'left'});
            $('.atf-follow > span',mainElement).hide();
            $('.atf-content',mainElement).css('max-height','260px');
        }else{
            if(atfViewport<400){
                $('.atf-content',mainElement).css('max-height','320px');
            }else{
                $('.atf-content',mainElement).css('max-height','none');
            }
            $('.atf-row-names img',mainElement).removeAttr('style');
            $('.atf-row-names',mainElement).removeAttr('style');
            $('.atf-row-names span',mainElement).removeAttr('style');
            $('.atf-show-media',mainElement).removeAttr('style');
            $('.atf-tweet-content',mainElement).removeAttr('style');
            $('.atf-retweet-notice',mainElement).removeAttr('style');
            $('.atf-tweet-actions',mainElement).removeAttr('style');
            $('.atf-tweet-metadata',mainElement).removeAttr('style');
            $('.atf-bottom',mainElement).removeAttr('style');
            $('.atf-follow > span',mainElement).removeAttr('style');
        }
    }

    var onresizeTimer;
    window.onresize = function() {
        clearTimeout(onresizeTimer);
        onresizeTimer = setTimeout(function() {
            $.each($('.atf-wrapper.atf'), function( index, value ){
                $(this).atfResponsive();
            });
        }, 300);
    };

    $(document).ready(function(e){
        $.each($('.atf-wrapper.atf'), function( index, value ){
            $('.atf-content',this).html('<div class="atf-loader">Loading...</div>');
            $(this).atfHttpRequest(true,false);
        });

    });
})(jQuery);
