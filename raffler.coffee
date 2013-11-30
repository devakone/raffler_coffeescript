window.Raffler=
	Models:{}
	Collections:{}
	Views:{}
	Routers:{}
	init: -> 
		new Raffler.Routers.Entries
		Backbone.history.start()

class Raffler.Routers.Entries extends Backbone.Router
  routes:
  	'':'index'
  	'entries/:id':'show'
  initialize: ->
  	@collection = new Raffler.Collections.Entries()
  	@collection.fetch()
  index: ->
  	view = new Raffler.Views.EntriesIndex(collection:@collection)
  	$('#name-list').html(view.render().el)
  show: (id) ->
  	console.log "Entry #{id}"

class Raffler.Views.EntriesIndex extends Backbone.View
  template: _.template($('#item-template').html())
  initialize: ->
  	@collection.on('sync', @render, this)
  render: ->
  	$(@el).html(@template(entries:@collection.toJSON()))
  	@

class Raffler.Collections.Entries extends Backbone.Collection
  url:'api.php?/rafflers'

$(document).ready ->
  Raffler.init()
