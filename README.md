# Buddyboss Videochat

Plugin to embed a Video Chat function based on Twilio Programmable Video within a BuddyBoss Wordpress Portal. Contact me for assistance to build this into your website.

## Changes

### Part 1

Insert in `bp-templates/bp-nouveau/buddypress/common/js-templates/messages/parts/bp-messages-single-header.php`:

Code before:
```php
        ...
      </dt>
      <dd>
        <span class="thread-date"><?php esc_html_e( 'Started', 'buddyboss' ); ?> {{data.started_date}}</span>
      </dd>
    </dl>
    <# } #>
```

Code added:
```php
    <input style="margin-left:auto" type="submit" value="Video Call" class="small" id="send_vc_notice_button"\>
```

Code after:
```php
    <div class="actions" style="margin-left: 20px" >
      <?php
        if ( bp_current_user_can( 'bp_moderate' ) ) {
          ?>
          <div class="message_actions">
            ...
```


### Part 2

Insert in `buddyboss-platform/bp-templates/bp-nouveau/js/buddypress-messages.js`:

Code before:
```javascript
      ...
      addEditor: function() {
        // Load the Editor.
        this.views.add( '#bp-message-content', new bp.Views.messageEditor() );
      },
```

Code added:
```javascript
      sendCallNotice: function(event) {
        var errors = [];
        event.preventDefault();

        if ( typeof tinyMCE !== 'undefined' ) {
          jQuery( tinyMCE.activeEditor.formElement ).addClass( 'loading' );
        } else if ( typeof bp.Nouveau.Messages.mediumEditor !== 'undefined' ) {
          jQuery( '#message_content' ).addClass( 'loading' );
        }

        this.model.set(
          {
            thread_id : this.options.thread.get( 'id' ),
            content   : "<span class=\"gray avatar-wrap\"><b>" + 
                        document.title.split(' – ')[1] +
                        "</b> is inviting you to a video call!</span>" + 
                        "<a class=\"small button outline\" target=\"_blank\" href=\"/video-chat/?id=" + 
                        this.options.thread.get('id') + 
                        "\">Click here to join</a>",
            sending   : true
          }
        );

        $( '#send_reply_button' ).prop( 'disabled',true ).addClass( 'loading' );
        $( '#send_vc_notice_button' ).prop( 'disabled',true ).addClass( 'loading' );

        this.collection.sync(
          'create',
          this.model.attributes,
          {
            success : _.bind( this.callNoticeDone, this ),
            error   : _.bind( this.replyError, this )
          }
        );
      },

      callNoticeDone: function (response) {
        var reply = this.collection.parse( response );
        this.model.set( 'sending', false );
        this.collection.add( _.first( reply ) );

        bp.Nouveau.Messages.removeFeedback();
        $( '#send_reply_button' ).prop( 'disabled',false ).removeClass( 'loading' );
        $( '#send_vc_notice_button' ).prop( 'disabled',false ).removeClass( 'loading' );

        $( '#bp-message-thread-list' ).animate( { scrollTop: $( '#bp-message-thread-list' ).prop( 'scrollHeight' )}, 0 );
      },
```

Code after:
```javascript
      sendReply: function( event ) {
        var errors = [];
        event.preventDefault();

        if ( true === this.model.get( 'sending' ) ) {
          return;
        }
        ...
```

### Part 3

Insert in `buddyboss-platform/bp-templates/bp-nouveau/js/buddypress-messages.js`:

Code before:
```
                                this.views.add(
					'#bp-message-load-more',
					new bp.Views.userMessagesLoadMore(
						{
							collection: this.collection,
							thread: this.options.thread,
							userMessage: this
							}
					)
				);
			},

			events: {
				'click #send_reply_button' : 'sendReply',
```

Code added:
```
                                'click #send_vc_notice_button' : 'sendCallNotice'
```

Code after:
```
                        },

			requestMessages: function() {
				var data 					   = {};
				this.options.collection.before = null;

				this.collection.reset();

				this.loadingFeedback = new bp.Views.MessagesLoading();
				this.views.add( '#bp-message-content',this.loadingFeedback );
```

### Part 4

Copy the whole contents of `buddyboss-platform/bp-templates/bp-nouveau/js/buddypress-messages.js` to `buddyboss-platform/bp-templates/bp-nouveau/js/buddypress-messages.min.js`

## Packaging

ZIP the files in this repository and upload as a plugin. Enter Twilio credentials under `BBVideo Options`.


## External Requirements

### Dependency

- owenl131/svelte-videochat

### WordPress Plugin Dependencies

- BuddyBoss Platform
- WP SMS Twilio Core

### Sound Effects

From http://freesoundeffect.net/
