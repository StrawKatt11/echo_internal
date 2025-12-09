extends PlayerState

func EnterState():
	Name = "Jump"
	Player.velocity.y = Player.jumpSpeed

func ExitState():
	pass

func Update(delta: float):
	Player.HandleGravity(delta)
	Player.HorizontalMovement()
	HandleJumpToFall()
	HandleAnimations()

func HandleJumpToFall():
	if (Player.velocity.y >= 0):
		Player.ChangeState(States.JumpPeak)

func HandleAnimations():
	Player.animator.play("Jump")
	Player.HandleFlipH()
