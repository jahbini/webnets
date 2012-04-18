(function($) {
    var tweetText,
    callback20,
    screen_name,
    tweet_input_area = $('#TweetBox [name=tweet]');
	    $("#addressee").hide().find('input').val('');
    function advise(t) {
        $('#TweetBoxLegend').text(t);
    }
    function adviseerror(t) {
        $('#TweetBoxError').html(t);
    }
    function remove(all) {
        if (all) {
            $('#TweetBox').removeClass('mobile').addClass('at_home').hide().css({
                position: "static"
            });
	    $("#addressee").hide().find('input').val('');
            $('#tweetlist').focus().removeClass('using').addClass('hidden');
        };
    }
    $("#action_copy").hover(function() {
        advise('Copy whole tweet to entry');
    }).click(
    function() {
        remove();
        fill_input(tweetText + ' ' + tweet_input_area.val())
    });
    $("#action_direct").hover(function() {
		    advise('Address as a direct (private) tweet');
		    }).click(
			    function() {
			    $("#addressee").show().find('input').val(screen_name);
			    });
    $("#action_retweet").hover(function() {
        advise('set as re-tweet');
    }).click(
    function() {
        remove();
        fill_input('RT @' + screen_name + " " + tweetText)
    });
    $("#action_goaway").hover(function() {
        advise('close pop-ups');
    }).click(
    function() {
        remove(true);
        fill_input("");
    });
    $("#action_FF").hover(function() {
        advise('Follow all users mentioned in tweet');
    }).click(
    function() {
        remove();
        $('#TweetBox [name=routing]').val('FollowAll');
	$('#TweetBox .mainButton').trigger('click');
    });
    $(".inaction").hover(function() {
        advise('');
    }).click(
    function() {
        remove();
    });
    $("#action_append").hover(function() {
        advise('append whole tweet to entry');
    }).click(
    function() {
        fill_input(tweet_input_area.val() + " " + tweetText);
    });
    $("#action_touser").hover(function() {
        advise('address to ' + screen_name);
    }).click( function() {
        remove();
        fill_input('@' + screen_name + ' ' + tweet_input_area.val());
    });

    $("#action_follow").hover(function() {
        advise('Follow ' + screen_name);
    }).click(function() {
        remove();
        $("#addressee").find('input').val(screen_name);
        $('#TweetBox [name=routing]').val('follow');
	$('#TweetBox .mainButton').trigger('click');
    });

    $("#action_unfollow").hover(function() {
        advise('Unfollow this Tweeter');
    }).click(function() {
        remove();
        fill_input("Please block and unfollow " + screen_name + " press Block/Unfollow to confirm");
        $('#TweetBox [name=routing]').val('unfollow');
        $("#addressee").find('input').val(screen_name);
        $('#TweetBox [name=action_smbtTweet]').val('Block/Unfollow');
    });
    function fill_input(value) {
    /*
        if (value.length > 140) {
            adviseerror('too long for Twitter: ' + value.slice(139,159));
            value = value.slice(0, 140);
        }
	*/
        tweet_input_area.val(value);
        $('#tweet_counter').text(140 - value.length);
        tweet_input_area.focus();
    }
    /* tweeting activity popup logic */
    $(".actOn a").live('click',
		function(e) {
		    /* do not bubble the event */
		    e.stopPropagation();
		    /* jquery sez it does it this way, but not in 3.2.2*/
		    if (stopPropagation) stopPropagation();
		    /*maybe the W3C stuff is implemented*/
		    /*but take the link and do not put up the #TweetBox */
		    return true;
		});

    $(".actOn").live('click',

    function(e) {
        tweet_input_area = jQuery('#TweetBox [name=tweet]');
        root = $(this).closest('[class*=screen_name__]');
        screen_name = root.attr('class').match(/screen_name__(\S+)/)[1];
        tweet_id = root.attr('class').match(/tweet_id_(\S+)/)[1];
        tweetText = root.find('span[class~=tweet_contents]').text();
        $('#TweetBox').removeClass('at_home').css({
            position: "absolute"
        }).addClass('mobile').animate({
            top: e.pageY - 175 + 'px',
            left: (e.pageX + 40) + "px"
        }).show();
        tweet_input_area.maxlength({
            'feedback': "#tweet_counter",
            'useInput': true
        });

        link = $(this).attr('href');
        return false;
    }
    
    );


    /*  DEBUG function
$("*", document.body).live('click',function (e) {
      var offset = $(this).offset();
      var position = $(this).position();
      e.stopPropagation();
      $("#result").text(this.tagName + ' #' + $(this).attr('id')  + " offset coords ( " + offset.left + ", " + offset.top + " ) " +
				" position coords ( " + position.left + ", " + position.top + " ) ");
    });
 */
    /* make tweetlist sticky when user  rolls over the window */
    $('#tweetlist').hover(function() {
        $(this).addClass('hold').css({
            background: '#ffeeff'
        }).fadeTo('fast', .99);
    },
    function() {
        $(this).removeClass('hold').filter(':not(.using)').addClass('hidden');
    });
    function enterPix(eve) {
        var me = $(this);
        var sn = me.attr('class').match(/\bscreen_name__(\S+)/)[1];
        var user = $.tweeters.get_by_screen_name(sn);
	
    function advise(f){
	    $("#list_advise").text(f);
    }
    function tweetlist_actions(){
	    var actions ="";
	    actions += '<span id="list_advise"></span>';
	    actions += '<div id="list_error"></div>';
	    return actions;
    };
        c = 'time_stale';
        var oldtime = me.attr('class').match(/\b(time_\w+)/);
        if (oldtime) {
            var oldclass = oldtime[1];
            if (me.hasClass(oldclass)) me.removeClass(oldclass);
        }
        me.addClass(c);
        var offset = me.offset();
        var left_margin = $('.typography').offset().left;
        var placement = offset.left - 350 + 52;
        if (placement < left_margin) placement = left_margin;
        $('#tweetlist').removeClass('hidden').html(tweetlist_actions() + user.texts())
		.prepend( $('<div class="listaction">?</div>')
				.hover(function() { advise('Show statistics for '+ user.screen_name); })
				.click( function() { remove();
						    $('.user_description').toggle();
					    })
			)
		.prepend( $('<div class="listaction">20</div>')
				.hover(function() { advise('Get 20 latest tweets for '+ user.screen_name); })
				.click( function() { remove();
						    $.getJSON('http://twitter.com/statuses/user_timeline.json?screen_name='
							+ user.screen_name + '&callback=?', function(f){ callback20(f); me.each(enterPix) });
					    })
			)
		.prepend ( $('<div class="listaction">F</div>')
				.hover(function() {advise('Follow ' + user.screen_name + ' as ' + $( '#TweetBox [name=penName] :selected').text());})
				.click(function() { remove();
							$("#addressee").find('input').val(user.screen_name);
							$('#TweetBox [name=routing]').val('follow');
							$('#TweetBox .mainButton').trigger('click');
						    })
			)
		.prepend( $('<div class="listaction">U</div>')
				.hover(function() {advise('Unfollow ' +user.screen_name);})
				.click(function(e) {remove();
							$("#tweetlist .actOn :first").trigger(e);
							fill_input("Unfollow " + user.screen_name + " press Unfollow to confirm");
							$('#TweetBox [name=routing]').val('unfollow');
							$("#addressee").find('input').val(user.screen_name);
							$('#TweetBox [name=action_smbtTweet]').val('Unfollow');
						    })
			)
		.prepend( $('<div class="listaction">B</div>')
				.hover(function() {advise('Block and Unfollow ' +user.screen_name);})
				.click(function(e) {remove();
							$("#tweetlist .actOn :first").trigger(e);
							fill_input("Please block and unfollow " + user.screen_name + " press Block/Unfollow to confirm");
							$('#TweetBox [name=routing]').val('block');
							$("#addressee").find('input').val(user.screen_name);
							$('#TweetBox [name=action_smbtTweet]').val('Block/Unfollow');
						})
			)
		.prepend( $('<div class="listaction">@</div>')
				.hover(function() {advise('Tweet to ' +user.screen_name);})
				.click(function(e) {remove();
							$("#tweetlist .actOn :first").trigger(e);
							fill_input("@" + user.screen_name);
							$("#addressee").find('input').val('');
						       	$('#TweetBox [name=routing]').val('tweet');
						       	$('#TweetBox [name=action_smbtTweet]').val('Tweet it!');
						})
			)
		.click(function() { $(this).addClass('using'); })
		.css({
            'z-index': "500",
            background: 'ivory',
            width: "350px",
            position: "absolute",
            top: (offset.top + 45) + "px",
            left: placement + "px"
        }).
        fadeTo('slow', .85);

    };
    function leavePix(me) {
        var me = $(this);
        if (!$('#tweetlist').hasClass('hold')) $('#tweetlist').addClass('hidden');
        var sn = me.attr('class').match(/\bscreen_name__(\S+)/)[1];
        var user = $.tweeters.get_by_screen_name(sn);
        var time_ = user.most_recent_status;
        var c = $.relative_class(time_);
        if (me.hasClass(c)) return;
        var oldtime = me.attr('class').match(/\b(time_\w+)/);
        if (oldtime) {
            var oldclass = oldtime[1];
            if (me.hasClass(oldclass)) me.removeClass(oldclass);
        }
        me.addClass(c);
    };

    $.extend({
        tweeters: {
            screen_name: []
        },
        relative_class: function(C) {
            var D = (arguments.length > 1) ? arguments[1] : new Date();
            var E = parseInt((D.getTime() - C) / 1000);
            E = E + (D.getTimezoneOffset() * 60);
            if (E < 60) {
                return "time_newest"
            } else {
                if (E < 120) {
                    return "time_newest"
                } else {
                    if (E < (60 * 60)) {
                        return "time_new"
                    } else {
                        if (E < (120 * 60)) {
                            return "time_stale"
                        } else {
                            if (E < (24 * 60 * 60)) {
                                return "time_old"
                            } else {
                                if (E < (48 * 60 * 60)) {
                                    return "time_yesterday"
                                } else {
                                    return "time_historical"
                                }
                            }
                        }
                    }
                }
            }
        },
	now_time_binary:function(){ var D = new Date(); return Math.floor(D.getTime() / 1000); },
        relative_time: function(C) {
            var D = (arguments.length > 1) ? arguments[1] : new Date();
            var E = parseInt((D.getTime() - $.absolute_time(C)) / 1000);
            E = E + (D.getTimezoneOffset() * 60);
            if (E < 60) {
                return "less than a minute ago"
            } else {
                if (E < 120) {
                    return "about a minute ago"
                } else {
                    if (E < (60 * 60)) {
                        return (parseInt(E / 60)).toString() + " minutes ago"
                    } else {
                        if (E < (120 * 60)) {
                            return "about an hour ago"
                        } else {
                            if (E < (24 * 60 * 60)) {
                                return "about " + (parseInt(E / 3600)).toString() + " hours ago"
                            } else {
                                if (E < (48 * 60 * 60)) {
                                    return "1 day ago"
                                } else {
                                    return (parseInt(E / 86400)).toString() + " days ago"
                                }
                            }
                        }
                    }
                }
            }
        },

        set_all_fades: function() {
            $(".tweetPix").each(function() {
                var me = $(this);
                var sn = me.attr('class').match(/\bscreen_name__(\S+)/)[1];
                var user = $.tweeters.get_by_screen_name(sn);
                var time_ = user.most_recent_status;
                var c = $.relative_class(time_);
                if (me.hasClass(c)) return;
                var oldtime = me.attr('class').match(/\b(time_\w+)/);
                if (oldtime) {
                    var oldclass = oldtime[1];
                    if (me.hasClass(oldclass)) me.removeClass(oldclass);
                }
                me.addClass(c);
                return;
            });

            setTimeout($.set_all_fades, 59 * 1000);
        },

        absolute_time: function(C) {
            var B = C.split(" ");
            return Date.parse(B[1] + " " + B[2] + ", " + B[5] + " " + B[3]);
        }
    });
    $.set_all_fades();
    $.extend($.tweeters, {
        update: function(s) {
            var new_name = s.screen_name;
            if (new_name in this.screen_name) {
                var old = this.screen_name[new_name];
                var st = s.status;
                new_key = $.absolute_time(st.created_at);
                if (old.statuses == undefined) {
                    //alert("bad status for screen_name " + new_name);
                    return;
                }
		if ('follows' in s) {
			if(!old.follows) old.follows =  { };
		       	old.follows[s.follows] = 'follows';
			delete s.follows;
		}
		if ('friend_of' in s) {
			if(!old.friend_of) old.friend_of =  { };
			old.friend_of[s.friend_of] = 'friend_of';
			delete s.friend_of;
		}
		if ('mentions' in s) {
			if(!old.mentions) old.mentions =  { };
			old.mentions[s.mentions] = 'mentions';
			delete s.mentions;
		}

                if (new_key in old.statuses) {
                    return old;
                }
		delete s.profile_link_color;
		delete s.profile_background_color;
		delete s.profile_border_color;
		delete s.profile_sidebar_fill_color;
		delete s.profile_sidebar_border_color;
		delete s.profile_background_image_url;
		delete s.profile_text_color;
                delete s.status;
                if (old.most_recent_status < new_key) {
                    old.most_recent_status = new_key;
                    old.changed = true;
                }


                for (key in s) { // go through the user keys
		    if (typeof s[key] ==='function') continue;
                    old[key] = s[key];
                }
                //update the top elements
                old.statuses[new_key] = st;
                s = old;
            } else {
                var ns = s.status;
                delete s.status;
                s.statuses = [];
		s.shown=0;
                var t = $.absolute_time(ns.created_at);
		var tmp;
		tmp=s.follows;
		if ('follows' in s) {
			s.follows={};
			if (typeof tmp === 'string') { s.follows[tmp] = 'follows';}
			if (typeof tmp === 'object') { s.follows = tmp;}
		}
		tmp=s.friend_of;
		if ('friend_of' in s) {
			s.friend_of={};
			if (typeof tmp === 'string') { s.friend_of[tmp] = 'friend_of';}
			if (typeof tmp === 'object') { s.friend_of = tmp;}
		}
		tmp=s.mentions;
		if ('mentions' in s) {
			//alert(tmp);
			s.mentions={};
			if (typeof tmp === 'string') { s.mentions[tmp] = 'mentions';}
			if (typeof tmp === 'object') { s.mentions = tmp;}
		}
                s.most_recent_status = t;
                s.statuses[t] = ns;
                s.changed = false;
                $.extend(s, {
                    texts: function() {
                        var i = 0,
			me=this,
                        ii = 0,
			lead_in = [],
			trends=0,
			retweets=0,
			linking=0,
			mentions=0,
			addresses=0,
			carbons=0,
			follow_worthy=0,
			followers=0,
			friends=0,
			all_tweets=1,
			gather_lead= function (t,text,filter) {
				var f=0;
				if (t in me && me[t] ) lead_in.push((text?text + ':':'')  + (f=(filter?filter(me[t]):me[t])));
				return f;
			},
			filter_link = function(text){
			    return (text?text.replace(
				    /(https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w\/~\-_\.]*(\?\S+)?)?)?)/g ,
					    "<a target='_blank' href='$1'>$1<\/a>"):'');
		       	},
                        tc = [],
                        keys = [],
			status_value,temp,txt,who,toggle=false,
			key,
			friend_follows ="",
                        header = ('<h3 class="actOn" >' + this.name + ' (' + this.screen_name + ')<\/h3>');
			gather_lead('description' );
			gather_lead('location','Where' );
			gather_lead('created_at','joined',$.relative_time );
			gather_lead('url','Website',filter_link);
			all_tweets= gather_lead('statuses_count','Tweets');
			followers = gather_lead('friends_count','Follows');
			friends   = gather_lead('followers_count','Followers');
			lead_in.push(lead_in.splice(-3,3).join(', '));
			follow_worthy = Math.sqrt(Math.sqrt(10000*Math.log(all_tweets)/(Math.abs(followers/friends - friends/followers) )));
                //for (key in this) { // go through the user keys
		 //   if (typeof this[key] !=='string') continue;
		  //  gather_lead(key,key + ' ' + this[key] );
                //}

			if(this.friend_of || this.follows){
			 friend_follows += "<p>";
			if(this.friend_of){
			friend_follows = friend_follows + "followed by: ";
		       	for (who in this.friend_of) if(this.friend_of[who] === 'friend_of' ) friend_follows += who + " ";
			}
			if(this.follows){ 
			if(this.friend_of) friend_follows += 'and ';
			friend_follows += 'follows: ' ;
		       	for (who in this.follows) if(this.follows[who] === 'follows' )  friend_follows += who + " ";
			}
			friend_follows += "<\/p>";
			}

                        // create the text each time the user rolls over the tweet.  Do not cache
                        for (key in this.statuses) {
			if ('text' in this.statuses[key]) keys.push(key);
                        }
                        // create an array of the times
                        keys.sort();
                        for (ii = 0; ii < keys.length; ii++) {
                            key = keys[ii];
			    status_value = this.statuses[key];
			    addresses += status_value.text.split(/^@/).length -1;
			    carbons += status_value.text.split(/@/).length -1;
			    retweets += status_value.text.split(/RT @/).length -1;
			    linking += status_value.text.split(/https?\:\/\//).length -1;
			    trends += status_value.text.split(/#/).length -1;
			    txt= filter_link(status_value.text);
			    if(this.mentions) {
				for(who in this.mentions) {
				//alert(status_value.text + " " + status_value.mentions[who] + " " + who);
					if(this.mentions[who] === 'mentions')
					{ var m
					    m= txt.replace(RegExp('(@'+who+')') , "<em class='mention'>$1<\/em>");
					    if(m !== txt) mentions ++;
					    txt=m;
				    }
				}
			    }
                            temp = "<li class='actOn " + ( (status_value.direct_message)?' private_message ':'' ) 
			    + "screen_name__" + this.screen_name + " tweet_id_" + this.id
                            + "'><span class='tweet_contents' >" +
			( (status_value.direct_message)?'<em class="mention">Directly to '+ status_value.recipient.screen_name+ '<\/em>: ':'' ) +
				txt
                            + "</span>:<em > " + $.relative_time(this.statuses[key].created_at) + ' <b>Use This<\/b><\/em><\/li>';
			    tc[i++] = temp;
                        }
                        tc = tc.reverse();
			var tweet_quality = keys.length/3 + addresses + retweets/2;
			tweet_quality += (linking+carbons - addresses)/3;
			tweet_quality  += trends/2;
			if (mentions) tweet_quality += mentions/1.4 ;
			tweet_quality = Math.floor(100* (tweet_quality / (keys.length/2))) /100.0;
			
			//lead_in.push("quality of tweets: " +  tweet_quality.toFixed(2));
			//lead_in.push("follow quotientary: " +  (follow_worthy/100).toFixed(2));
			lead_in.push("message analysis follow factor:  " + (tweet_quality*follow_worthy).toFixed(2));
			//lead_in.push("messages " +  keys.length);
			//lead_in.push("Addresses " + addresses );
			//lead_in.push("retweets " + retweets );
			//if (mentions) lead_in.push("mentions " + mentions );
			//lead_in.push("CC " + ( carbons -retweets - addresses));
			//lead_in.push("Topics " + trends );
			this.shown += 1;
                        return '<div class="tweet_text_overlay">' +
                        header  +
		       	((lead_in != [] )?'<ul class="user_description' + (this.shown>2?' hidden':'') + '"><li>' + lead_in.join('</li><li>') + '</li></ul>':'')
			+ friend_follows 
			+ '<ul>' + tc.join('&#013;').split('"').join('&#034;')
                        + '<\/ul><\/div>';
                    },
                    getImg: function() {
                        html = "<img class='tweetPix screen_name__" + this.screen_name + "' title='' alt='Image for " + this.screen_name + "' src='" + this.profile_image_url + "' />";

                        return $(html).hoverIntent(enterPix, leavePix).click(function(){
				screen_name = $(this).attr('class').match(/screen_name__(\S+)/)[1];
				if($('#slider .screen_name__'+ screen_name).length==0){ 
				    if($('#slider img').length > 15 ) {
				       $('#slider img:first').remove();
				    }
				    $('#slider').append($(this).clone(true) );
			        }
			    $('#tweetlist').addClass('hold').css({
				    background: '#ffeeff'
					}).fadeTo('fast', .99);
								});
                    }
                });
            };
            $.tweeters.screen_name[new_name] = s;
            return s;
        },
        decode_user: function(s) {
            var u = s;
            var x = "";
	    if ('sender' in s && 'recipient' in s ) { /* this is a direct message, we are interested in the sender, mostly */
		    u = s.sender;
		    delete s.sender;
		    u.status =s;
		    u.status.direct_message = true;
		    s=u;
	    } else { 
	   	s.direct_message = false;
		    s.recipient = { screen_name: 'public tweet' };
		   u=s;
	    }
	    if (! ('user' in s) && ! ('status' in  s) ) {/*  this is from search api, convert */
		    var t = s.created_at.split(' ');
		    var created = [ t[0], t[2], t[1] , t[4], t[5] , t[3] ].join(' ');
		    u= { status : { text : s.text, in_reply_to_screen_name: s.to_user, created_at: created, id: s.id, in_reply_to_user_id: s.to_user_id}
			, screen_name: s.from_user, name: 'Only Known as ',  profile_image_url: s.profile_image_url,  id: s.from_user_id};
		s={ recipient: 'public tweet', direct_message:false};
	    }
            if ('user' in s) {
                u = s.user;
                delete s.user;
                u.status = s;
		u.status.recipient ='public tweet';
		u.status.direct_message = false;
            }

            if (! ('status' in u)) return false;
            if (u.screen_name == 'invoke') {
                return null;
                alert('bogus user');
                return null;
            }
            return this.update(u);
        },
        get_by_screen_name: function(s) {
            sn = $.tweeters.screen_name;
            if (!s in sn) return null;
            return (sn[s]);
        }
    });

	var Throttle = function() {
			var clients= 0,
			client_state= [],
			rate= 'auto';
			function setRate(r){
			       $('.iconBox').removeClass('auto').removeClass('once').removeClass('off');
				switch(r) {
					case "off":
					case "OFF":
						rate = 'off';
						break;
					case "auto":
					case "AUTO":
						rate = 'auto';
						break;
					case "once":
					case "ONCE":
						rate = 'once';
						break;
					default:
						rate = 'off';
						break;
					}
				$('.iconBox').addClass(rate);
				$("input:radio[name=refreshx][value=" + rate + ']"').select();
			}	
			$("input:radio[name=refreshx]").click(function(){
				var i;
			       rate=$(this).attr('value');
				setRate(rate);
				if (rate == "auto")
					{  for (i=0; i<clients; i++) {
						if(client_state[i].activity == 'shot') client_state[i].activity="idle";
						}
					}
				if (rate == "once") {
					{  for (i=0; i<clients; i++) {
						if(client_state[i].activity == 'shot') client_state[i].activity="idle";
						}
					}
				}
			       return true;
		       });
			function activate () {
				var i,
				D=new Date();
				
				if ( rate == 'off' ) return;
				for( i=0; i< clients; i++ ) {
					if (client_state[i].activity == 'queued'  &&  D.getTime() > 120000+ client_state[i].lastfire) {
						if(confirm("client " + client_state[i].requestString + " seems hung refire?" )){
							client_state[i].lastfire = D.getTime();
							client_state[i].request = client_state[i].callback(); // fire! 
						} else {
							client_state[i].activity='shot';
						}
					}
					if (client_state[i].activity == 'idle'  &&  D.getTime() > client_state[i].waitInterval + client_state[i].lastfire) {
						client_state[i].activity = 'queued';
						client_state[i].lastfire = D.getTime();
						client_state[i].request = client_state[i].callback(); // fire! 
					}
				}
				
			}
			setInterval(activate, 2000);
			setRate("auto");
			return {
			setWait: function(client,w) {
if (w<0) {

				
			       $('.iconBox').removeClass('auto').removeClass('once').removeClass('off').addClass('off');
				$("input:radio[name=refreshx][value=off]").select();
w=90;
}
				client_state[client].waitInterval= 1000*w;	
				},
			subscribe: function(callback,requestString) {
				var D=new Date();
				client_state[clients] = {activity: "idle", waitInterval:1000*90, lastfire: 0, rate: rate, requestString:requestString, callback:callback, request: false};
				return clients++;
				},
			finished: function (client) {
				// a client has finished all action from a twitter request.  Do any end processing.
				var D=new Date(),i;
				client_state[client].lastfire = D.getTime();	
				client_state[client].activity=(rate=="once")?"shot":"idle";
//alert("Client #" + client + " new state is " + client_state[client].activity);
					// if we are in one-shot mode, wait till all clients have been queued and finished, then go to 'off'
				if(rate == 'once') {
					for(i=0; i<clients; i++) {
						if(client_state[i].activity != "shot") return;
					}
					rate = 'off'
					$("input:radio[name=refreshx][value=off]").select();
				}
			}
			
			}
			
}();

    $('.iconBox').each(function() {
        var waterfall = $(this);
        var divname = waterfall.attr('id');
        var current_stick = 0;
        var sticks = [];
        var name_position = [];
        var num_sticks = waterfall.find('.stick_holder').attr('class').match(/\bsticks__(\S+)/)[1];
        for (var i = 0; i < num_sticks; i++) {
            waterfall.find('.stick_holder').append("<div id='" + divname + "_" + i + "' class='sticks' > </div>")
        }
        var t = $('.sticks').width() * num_sticks;
        waterfall.width(t);
        waterfall.find('.sticks').attr('id',
        function(arr) {
            return divname + '_' + arr;
        })
        .each(function() {
            var me = $(this);
            sticks.push($.extend(me, {
                id: me.attr('id') + '_',
                stick_position: [],
                raw: [],
                get_element: function(p) {
                    return {
                        element:
                        $('#' + me.id + p).children(),
                        raw: me.raw[p]
                    }
                },
                set_element: function(p, raw) {
                    var rel_class = $.relative_class(raw.most_recent_status);
                    me.stick_position[p].html(raw.getImg().addClass(rel_class).each(function() {
                        if (rel_class == "time_newest") $(this).addClass('needs_action');
                    }))
                    ;
                    name_position[raw.screen_name] = {
                        position: p,
                        stick: me
                    };
                    me.raw[p] = raw;
                },
                extend_element: function(raw) {
                    var position = me.stick_position.length;
                    var id = me.id + position;
                    me.append(me.stick_position[position] = $('<div id="' + id + '" />'));
                    me.bubble_up(position, raw);
                },
                bubble_up: function(p, raw) {
                    me.set_element(p, raw);
                    if (p == 0) return true;
                    //  movement complete
                    var my_time = raw.most_recent_status;
                    var heap_top_position = (p - 1) >> 1;
                    var c = me.get_element(heap_top_position);
                    var heap_top_time = c.raw.most_recent_status;
                    if (heap_top_time == my_time) {
                        return true;
                        // we like where we are, and we belong there
                    }
                    if (heap_top_time > my_time) return true;
                    // we are done
                    if (heap_top_time < my_time) {
                        // switch places with heap top
                        var c = me.get_element(heap_top_position);
                        me.set_element(p, c.raw);
                        me.bubble_up(heap_top_position, raw);
                        // everything OK
                    }
                }

            }))
        });
        function stuffIn(raw) {
		if (!raw) return;
            if (raw.screen_name in name_position) {
                p = name_position[raw.screen_name];
                the_stick = p.stick;
                position = p.position;
                the_stick.bubble_up(position, raw);
            } else {
                current_stick = (current_stick ? current_stick: num_sticks) - 1;
                the_stick = sticks[current_stick];
                the_stick.extend_element(raw);
            }
        }
	function grabStructure() {
        var  since_id = false,i, myThrottle,RequestString=this.RequestString, refreshUrl = false,
        twitterCallback2 = function (C) {
            if (C.wait)  Throttle.setWait(myThrottle,C.wait);
	    if (C.since_id) since_id = C.since_id;
	    if (C.content) C = C.content;
	    if (C.results ) {
		    /* search api */
		    refreshUrl = C.refresh_url;
		    C=C.results;
	    }
		// Go through the resultant array and post the tweets
            for (var D = C.length - 1; D > -1; D--) {
                stuffIn( $.tweeters.decode_user(C[D]));
            }
	   Throttle.finished(myThrottle);
        };

	myThrottle = Throttle.subscribe( function() {
		if (refreshUrl) {
			RequestString = [ RequestString.split('?')[0],refreshUrl].join('');
			since_id = false;
		} 
                return $.getJSON(RequestString + (since_id?"&since_id=" + since_id:'') + '&callback=?', twitterCallback2);
            },RequestString );

	callback20=twitterCallback2;
	}
        // start the fetching of tweeters/tweets in this waterfall
        $(eval('(' + waterfall.find('.tweetcommand').text() + ')' )).each(grabStructure);

    });
    /* change the pen name via ajax and the dropdown  field 'penName' */
    var form = $('#penName').closest('form');
    var textarea = form.find('[name=tweet]');
    $('#penName select').change(
    function() {
        b = $(this).fadeTo("slow", .50).val();
        $.getScript('home/changePenName/' + b,
        function(result) {
            jQuery('#penName select').fadeTo("slow", .99);
        });
        return false;
    });
    tweet_input_area = jQuery('#TweetBox [name=tweet]');


    /* send the tweet by ajax to  the host */
    $(form).find('.mainButton').click( function(event) {
        var formAct = form.attr('action') ;
	var formData = $(this).fieldSerialize();
	var formAction = formAct + '?' + formData;
        /*  WAS JAH -- var formAction = form.attr('action') + '?' + $(this).fieldSerialize(); */
        /* Post the data to save */
        $('#TweetBoxLegend').fadeTo('fast', .33);
        $.post(formAction, form.formToArray(),
		// process the result from the server
        function(result) {
	    $("#addressee").hide().find('input').val('');
            jQuery('#TweetBoxLegend').text(result).fadeTo('slow', .99);
        });
        $('#TweetBox [name=routing]').val('tweet');
        $('#TweetBox [name=action_smbtTweet]').val('Tweet it!');
        event.stopPropagation();
	event.preventDefault();
        return false;
    });

    /* tie the Tweetbox to the maxlength routine */
    tweet_input_area.maxlength({
        'feedback': "#tweet_counter",
        'useInput': true
    });

    $("input.pcreator").bind("change","",function() {
		    p=$(this).parent();
		    if ($(this).is(':checked')) {
		    $(this).next().text('Will create new pane.');
		    p.nextAll().fadeTo('fast',0.99);
		    } else {
		    p.nextAll().fadeTo('slow',0.33);
		    $(this).next().text('Create new pane?');
		    }
		    });

    $("input.pdeletor").bind("change","",function() {
		    p=$(this).parent();
		    if ($(this).is(':checked')) {
		    p.nextAll().fadeTo('slow',0.33).find(':checkbox').attr('disabled','true');
		    $(this).next().text('Pane will be deleted.');
		    } else {
		    p.nextAll().fadeTo('fast',0.99).find(':checkbox').removeAttr('disabled');
		    $(this).next().text('Delete this pane?');
		    }
		    });

    $("input.vcreator").bind("change","",function() {
		    if ($(this).is(':checked')) {
		    $(this).next().text('Will create new visual').nextAll().fadeTo('fast',0.99);
		    } else {
		    $(this).next().text('Create new visual').nextAll().fadeTo('slow',0.33);
		    }
		    });


    $("input.vdeletor").bind("change","",function() {
		    if ($(this).is(':checked')) {
		    $(this).next().text('visual to be deleted').nextAll().fadeTo('slow',0.33);
		    } else {
		    $(this).next().text('Delete this visual?').nextAll().fadeTo('fast',0.99);
		    }
		    });
    $("input.vcreator,input.vdeletor,input.pcreator,input.pdeletor").change();

} (jQuery));
